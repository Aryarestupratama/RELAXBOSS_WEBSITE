<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Intent;

/** Hasil membaca keluaran model. Bila bukan JSON: `reply` berisi teks mentah dan sisanya kosong. */
final readonly class ParsedChatReply
{
    public function __construct(
        public string $reply,
        public ?Intent $intent = null,
        public ?float $confidence = null,
        public ?string $strategy = null,
        public ?string $flowAction = null,
    ) {}
}
