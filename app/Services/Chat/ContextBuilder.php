<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\AiFeature;
use App\Enums\Intent;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Ai\MajorContext;
use App\Services\Ai\PromptLoader;
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
    ) {}

    /**
     * @return list<array{role: string, content: string}>
     */
    public function build(User $user, Conversation $conversation): array
    {
        if ($conversation->user_id !== $user->id) {
            throw new LogicException('Percakapan bukan milik pengguna ini.');
        }

        $system = str_replace(
            '{{INTENT_CODES}}',
            implode(', ', Intent::codes()),
            $this->prompts->load(AiFeature::Chat)->content,
        );

        $system .= $this->major->line($user->major) ?? '';

        return [['role' => 'system', 'content' => $system], ...$this->history($conversation)];
    }

    /**
     * Pesan terakhir (maks. `context_max_messages`), lalu dipotong dari yang tertua bila total
     * karakter melebihi `context_max_chars`. Pesan terbaru selalu ikut.
     *
     * @return list<array{role: string, content: string}>
     */
    private function history(Conversation $conversation): array
    {
        $maxMessages = (int) config('relaxboss.limits.context_max_messages');
        $maxChars = (int) config('relaxboss.limits.context_max_chars');

        $rows = $conversation->messages()
            ->reorder()
            ->orderByDesc('turn_number')
            ->limit($maxMessages)
            ->get();

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
