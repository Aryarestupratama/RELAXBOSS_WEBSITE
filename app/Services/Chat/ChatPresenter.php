<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\Intent;
use App\Enums\MessageRole;
use App\Enums\SuggestionType;
use App\Models\Conversation;
use App\Models\Message;
use App\Support\Wib;

/** Bentuk data Percakapan untuk klien (SCR-017). Isi pesan hanya dikirim ke pemiliknya. */
final class ChatPresenter
{
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * @return array{id: int, role: string, content: string, time: string, is_crisis: bool, suggestion: array{type: string, priority: int, dismissible: bool}|null}
     */
    public function message(Message $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role->value,
            'content' => $message->content,
            'time' => Wib::toWib($message->created_at)->format('H.i').' WIB',
            'is_crisis' => $message->is_crisis,
            'suggestion' => $message->role === MessageRole::Assistant ? $this->suggestion($message) : null,
        ];
    }

    /** True bila pesan terakhir adalah pesan pengguna yang belum dijawab (mis. AI gagal). */
    public function hasUnanswered(Conversation $conversation): bool
    {
        $last = $conversation->messages()->reorder()->orderByDesc('turn_number')->first();

        return $last !== null && $last->role === MessageRole::User;
    }

    /** "5 Oktober 2026, 14.30 WIB" dari `created_at`. */
    public function dateLabel(Conversation $conversation): string
    {
        $wib = Wib::toWib($conversation->created_at);

        return $wib->day.' '.self::MONTHS[$wib->month].' '.$wib->year.', '.$wib->format('H.i').' WIB';
    }

    /** @return array{type: string, priority: int, dismissible: bool}|null */
    private function suggestion(Message $message): ?array
    {
        $type = $message->suggestion_type;

        if ($type === null) {
            return null;
        }

        $intent = $message->detected_intent !== null ? Intent::tryFrom($message->detected_intent) : null;

        return [
            'type' => $type->value,
            'priority' => $intent?->priority() ?? ($type === SuggestionType::EscalateCrisis ? 1 : 3),
            'dismissible' => $type !== SuggestionType::EscalateCrisis,
        ];
    }
}
