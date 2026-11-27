<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\CarbonImmutable;

/**
 * Opsi ekspor data AI yang diteruskan ke eksportir (perintah `relaxboss:export-ai-data` dan ekspor Excel di Admin).
 * `since` (termasuk) dan `until` (tidak termasuk) adalah batas waktu UTC; keduanya boleh kosong.
 */
final readonly class ExportOptions
{
    public function __construct(
        public ?CarbonImmutable $since = null,
        public bool $excludeCrisis = false,
        public ?CarbonImmutable $until = null,
    ) {}
}
