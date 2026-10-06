<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\MessageRole;
use App\Models\User;
use App\Support\Wib;

/** Batas pesan RelaxMate per hari WIB (FR-016). Batas per menit ada di limiter `chat`. */
final class ChatQuota
{
    public function limit(): int
    {
        return (int) config('relaxboss.limits.chat_per_day');
    }

    /** Jumlah pesan pengguna hari ini (WIB), lintas Percakapan, lewat relasi pemilik (RULE-033). */
    public function usedToday(User $user): int
    {
        $startUtc = Wib::startOfDay()->utc();

        return $user->chatMessages()
            ->where('messages.role', MessageRole::User->value)
            ->where('messages.created_at', '>=', $startUtc)
            ->count();
    }

    public function remainingToday(User $user): int
    {
        return max(0, $this->limit() - $this->usedToday($user));
    }
}
