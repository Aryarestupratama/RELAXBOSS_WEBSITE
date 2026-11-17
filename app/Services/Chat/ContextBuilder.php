<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\DTOs\ChatContext;
use App\Enums\AiFeature;
use App\Enums\Intent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Ai\MajorContext;
use App\Services\Ai\PromptLoader;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Menyusun pesan yang dikirim ke AI: system prompt + jurusan + riwayat terpotong (FR-015, FR-026).
 *
 * Yang boleh dikirim hanya isi percakapan dan jurusan (bila diisi). Nama, email, ID, dan kampus
 * tidak pernah dibaca di sini (RULE-041). Jurusan adalah teks bebas dari pengguna, jadi
 * dibersihkan, dipotong, dan diberi penanda sebagai data (bukan instruksi).
 */
final class ContextBuilder
{
    public function __construct(
        private readonly PromptLoader $prompts,
        private readonly MajorContext $major,
        private readonly ConversationPlanner $planner,
    ) {}

    /**
     * Pesan untuk AI beserta rencana giliran (jenis balasan, boleh bertanya atau tidak) yang dipilih
     * `ConversationPlanner` dari riwayat. Arahan giliran ditambahkan ke system prompt, sebelum baris jurusan.
     * `$retry` dipakai saat balasan pertama melanggar larangan bertanya.
     */
    public function build(User $user, Conversation $conversation, bool $retry = false): ChatContext
    {
        if ($conversation->user_id !== $user->id) {
            throw new LogicException('Percakapan bukan milik pengguna ini.');
        }

        $rows = $this->recentRows($conversation);

        $plan = $this->planner->plan($this->turns($rows));

        if ($retry) {
            $plan = $plan->forRetry();
        }

        $system = str_replace(
            ['{{INTENT_CODES}}', '{{STRATEGY_CODES}}', '{{FLOW_ACTIONS}}'],
            [implode(', ', Intent::codes()), implode(', ', ChatReplySchema::STRATEGIES), implode(', ', ChatReplySchema::FLOW_ACTIONS)],
            $this->prompts->load(AiFeature::Chat)->content,
        );

        $system .= $plan->section();
        $system .= $this->major->line($user->major) ?? '';

        return new ChatContext([['role' => 'system', 'content' => $system], ...$this->history($rows)], $plan);
    }

    /**
     * @return Collection<int, Message>
     */
    private function recentRows(Conversation $conversation): Collection
    {
        return $conversation->messages()
            ->reorder()
            ->orderByDesc('turn_number')
            ->limit((int) config('relaxboss.limits.context_max_messages'))
            ->get();
    }

    /**
     * Riwayat lama ke baru untuk planner.
     *
     * @param  Collection<int, Message>  $rows  terbaru lebih dulu
     * @return list<array{role: string, content: string, strategy: ?string, flow_action: ?string}>
     */
    private function turns(Collection $rows): array
    {
        return $rows->reverse()->values()->map(static fn (Message $row): array => [
            'role' => $row->role->value,
            'content' => $row->content,
            'strategy' => $row->ai_strategy,
            'flow_action' => $row->ai_flow_action,
        ])->all();
    }

    /**
     * Pesan terakhir (maks. `context_max_messages`), lalu dipotong dari yang tertua bila total
     * karakter melebihi `context_max_chars`. Pesan terbaru selalu ikut.
     *
     * @param  Collection<int, Message>  $rows  terbaru lebih dulu
     * @return list<array{role: string, content: string}>
     */
    private function history(Collection $rows): array
    {
        $maxChars = (int) config('relaxboss.limits.context_max_chars');

        $kept = [];
        $total = 0;

        foreach ($rows as $row) {
            $content = $row->content;
            $length = mb_strlen($content);

            if ($kept === []) {
                $content = mb_substr($content, 0, $maxChars);
                $length = mb_strlen($content);
            } elseif ($total + $length > $maxChars) {
                break;
            }

            $kept[] = ['role' => $row->role->value, 'content' => $content];
            $total += $length;
        }

        return array_reverse($kept);
    }
}
