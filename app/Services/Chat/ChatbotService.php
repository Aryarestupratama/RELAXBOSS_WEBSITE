<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Contracts\ChatCompletionClient;
use App\DTOs\ChatbotResult;
use App\Enums\AiFeature;
use App\Enums\MessageRole;
use App\Exceptions\AiUnavailableException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Satu giliran RelaxMate: simpan pesan pengguna, minta balasan AI, simpan balasan (FR-014, FR-029).
 *
 * Pemanggil (ChatController, TASK-020) yang memeriksa kepemilikan Percakapan, consent, rate limit,
 * panjang pesan, dan kunci per pengguna. Pesan pengguna disimpan lebih dulu, jadi bila AI gagal
 * (AiUnavailableException) pesannya tetap tersimpan dan `total_turns` tidak bertambah.
 * `role` menentukan peran pesan; `turn_number` hanya pengurut (ganjil/genap tidak dijamin
 * setelah ada kegagalan AI).
 */
final class ChatbotService
{
    public function __construct(
        private readonly ChatCompletionClient $client,
        private readonly ContextBuilder $context,
        private readonly ChatReplyParser $parser,
        private readonly ActionSuggestionService $suggestions,
    ) {}

    /**
     * @throws AiUnavailableException
     */
    public function handle(User $user, Conversation $conversation, string $content): ChatbotResult
    {
        $userMessage = $this->storeUserMessage($conversation, $content);

        return $this->respond($user, $conversation, $userMessage);
    }

    public function storeUserMessage(Conversation $conversation, string $content): Message
    {
        return DB::transaction(function () use ($conversation, $content): Message {
            $message = $conversation->messages()->create([
                'turn_number' => $this->nextTurnNumber($conversation),
                'role' => MessageRole::User,
                'content' => $content,
            ]);

            $conversation->forceFill(['last_message_at' => now('UTC')])->save();

            return $message;
        });
    }

    /**
     * Meminta balasan untuk pesan pengguna yang sudah tersimpan (juga dipakai untuk mengulang
     * setelah AI gagal).
     *
     * @throws AiUnavailableException
     */
    public function respond(User $user, Conversation $conversation, Message $userMessage): ChatbotResult
    {
        $messages = $this->context->build($user, $conversation);
        $completion = $this->client->complete(AiFeature::Chat, $messages);
        $parsed = $this->parser->parse($completion->content);

        $type = $this->suggestions->typeFor($parsed->intent);
        $suggestion = $this->suggestions->suggestion($parsed->intent);

        $assistantMessage = DB::transaction(function () use ($conversation, $parsed, $type): Message {
            $message = $conversation->messages()->create([
                'turn_number' => $this->nextTurnNumber($conversation),
                'role' => MessageRole::Assistant,
                'content' => $parsed->reply,
                'detected_intent' => $parsed->intent?->value,
                'detection_confidence' => $parsed->confidence,
                'ai_strategy' => $parsed->strategy,
                'ai_flow_action' => $parsed->flowAction,
                'suggestion_type' => $type,
            ]);

            $conversation->forceFill([
                // Intent pesan pertama, diisi sekali.
                'initial_intent' => $conversation->total_turns === 0
                    ? ($parsed->intent?->value ?? $conversation->initial_intent)
                    : $conversation->initial_intent,
                'total_turns' => $conversation->total_turns + 1,
                // Intent darurat dari AI menyalakan banner bantuan permanen (lapisan 2, FR-018).
                'has_crisis' => $conversation->has_crisis || ($parsed->intent?->isCrisis() ?? false),
                'last_message_at' => now('UTC'),
            ])->save();

            return $message;
        });

        return new ChatbotResult($userMessage, $assistantMessage, $suggestion);
    }

    private function nextTurnNumber(Conversation $conversation): int
    {
        return (int) $conversation->messages()->reorder()->max('turn_number') + 1;
    }
}
