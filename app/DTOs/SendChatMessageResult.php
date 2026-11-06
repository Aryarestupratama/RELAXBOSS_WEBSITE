<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Message;

/** Hasil `SendChatMessage` yang berhasil: kedua pesan, status krisis Percakapan, dan sisa kuota hari ini. */
final readonly class SendChatMessageResult
{
    public function __construct(
        public Message $userMessage,
        public Message $assistantMessage,
        public bool $hasCrisis,
        public int $remainingToday,
    ) {}
}
