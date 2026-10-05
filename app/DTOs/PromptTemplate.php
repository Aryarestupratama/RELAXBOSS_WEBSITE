<?php

declare(strict_types=1);

namespace App\DTOs;

/** Prompt sistem satu fitur AI. `version` berasal dari baris versi di berkas (bukan dari isi). */
final readonly class PromptTemplate
{
    public function __construct(
        public string $content,
        public string $version,
        public bool $isPlaceholder,
    ) {}
}
