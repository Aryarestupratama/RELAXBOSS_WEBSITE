<?php

declare(strict_types=1);

namespace App\DTOs;

/** Hasil satu panggilan AI yang berhasil. */
final readonly class AiCompletion
{
    public function __construct(
        public string $content,
        public string $model,
        public int $promptTokens,
        public int $completionTokens,
    ) {}
}
