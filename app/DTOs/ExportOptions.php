<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonImmutable;

/** Opsi perintah `relaxboss:export-ai-data` yang diteruskan ke eksportir. */
final readonly class ExportOptions
{
    public function __construct(
        public ?CarbonImmutable $since = null,
        public bool $excludeCrisis = false,
    ) {}
}
