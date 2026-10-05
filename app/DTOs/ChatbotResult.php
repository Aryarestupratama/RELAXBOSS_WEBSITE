<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Message;

/** Hasil satu giliran chat yang berhasil. */
final readonly class ChatbotResult
{
    /**
     * @param  array{type: string, priority: int, dismissible: bool}|null  $suggestion
     */
    public function __construct(
        public Message $userMessage,
        public Message $assistantMessage,
        public ?array $suggestion,
        /** True bila giliran ini dijawab Crisis Response statis oleh CrisisDetector (AI tidak dipanggil). */
        public bool $isCrisis = false,
    ) {}
}
