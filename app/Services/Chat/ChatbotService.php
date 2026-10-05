<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Contracts\ChatCompletionClient;
use App\DTOs\ChatbotResult;
use App\Enums\AiFeature;
use App\Enums\CrisisLayer;
use App\Enums\Intent;
use App\Enums\MessageRole;
use App\Exceptions\AiUnavailableException;
use App\Models\Conversation;
use App\Models\CrisisEvent;
use App\Models\Message;
use App\Models\User;
use App\Support\CrisisResponse;
use Illuminate\Support\Facades\DB;

/**
 * Satu giliran RelaxMate: simpan pesan pengguna, minta balasan AI, simpan balasan (FR-014, FR-029).
 *
 * Pemanggil (ChatController, TASK-020) yang memeriksa kepemilikan Percakapan, consent, rate limit,
 * panjang pesan, dan kunci per pengguna. Pesan pengguna disimpan lebih dulu, jadi bila AI gagal
 * (AiUnavailableException) pesannya tetap tersimpan dan `total_turns` tidak bertambah.
 * `role` menentukan peran pesan; `turn_number` hanya pengurut (ganjil/genap tidak dijamin
 * setelah ada kegagalan AI).
 *
 * Pesan pengguna diperiksa `CrisisDetector` lebih dulu (FR-018 lapisan 1). Bila terindikasi krisis,
 * AI tidak dipanggil: Crisis Response statis disimpan, `has_crisis` menyala, dan `crisis_events`
 * (hanya hitungan, tanpa isi dan tanpa pengguna) dicatat.
 */
final class ChatbotService
{
    public function __construct(
        private readonly ChatCompletionClient $client,
        private readonly ContextBuilder $context,
        private readonly ChatReplyParser $parser,
        private readonly ActionSuggestionService $suggestions,
        private readonly CrisisDetector $crisis,
    ) {}

    /**
     * @throws AiUnavailableException
     */
    public function handle(User $user, Conversation $conversation, string $content): ChatbotResult
    {
        $crisisIntent = $this->crisis->detect($content);
        $userMessage = $this->storeUserMessage($conversation, $content, $crisisIntent !== null);

        if ($crisisIntent !== null) {
            return $this->respondToCrisis($conversation, $userMessage, $crisisIntent);
        }

        return $this->respond($user, $conversation, $userMessage);
    }

    public function storeUserMessage(Conversation $conversation, string $content, bool $isCrisis = false): Message
    {
        return DB::transaction(function () use ($conversation, $content, $isCrisis): Message {
            $message = $conversation->messages()->create([
                'turn_number' => $this->nextTurnNumber($conversation),
                'role' => MessageRole::User,
                'content' => $content,
                'is_crisis' => $isCrisis,
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
        // Pesan krisis tidak pernah dikirim ke AI, termasuk saat dipanggil ulang.
        if ($userMessage->is_crisis) {
            return $this->respondToCrisis(
                $conversation,
                $userMessage,
                $this->crisis->detect($userMessage->content) ?? Intent::CrisisSuicide,
            );
        }

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

    /**
     * Crisis Response statis tanpa memanggil AI. Daftar kontak tidak disimpan di pesan: dirender
     * klien dari `config('relaxboss.crisis_contacts')`.
     */
    private function respondToCrisis(Conversation $conversation, Message $userMessage, Intent $intent): ChatbotResult
    {
        $type = $this->suggestions->typeFor($intent);
        $suggestion = $this->suggestions->suggestion($intent);

        $assistantMessage = DB::transaction(function () use ($conversation, $intent, $type): Message {
            $message = $conversation->messages()->create([
                'turn_number' => $this->nextTurnNumber($conversation),
                'role' => MessageRole::Assistant,
                'content' => CrisisResponse::TEXT,
                'detected_intent' => $intent->value,
                'suggestion_type' => $type,
                'is_crisis' => true,
            ]);

            $conversation->forceFill([
                'initial_intent' => $conversation->total_turns === 0
                    ? $intent->value
                    : $conversation->initial_intent,
                'total_turns' => $conversation->total_turns + 1,
                'has_crisis' => true,
                'last_message_at' => now('UTC'),
            ])->save();

            // Hanya hitungan: tanpa isi, tanpa pengguna, tanpa pesan (ENT-009).
            CrisisEvent::query()->create(['layer' => CrisisLayer::Rule]);

            return $message;
        });

        return new ChatbotResult($userMessage, $assistantMessage, $suggestion, isCrisis: true);
    }

    private function nextTurnNumber(Conversation $conversation): int
    {
        return (int) $conversation->messages()->reorder()->max('turn_number') + 1;
    }
}
