<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Models\Conversation;
use App\Models\User;

/**
 * API-014: mulai Percakapan. Bila pengguna masih punya Percakapan kosong (belum ada pesan),
 * itu yang dipakai, supaya tombol "Mulai percakapan baru" tidak menumpuk Percakapan kosong.
 * Selalu lewat relasi pemilik (RULE-033).
 */
final class StartConversation
{
    public function handle(User $user): Conversation
    {
        $empty = $user->conversations()
            ->whereNull('last_message_at')
            ->latest('created_at')
            ->first();

        return $empty ?? $user->conversations()->create();
    }
}
