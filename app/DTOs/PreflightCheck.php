<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Satu hasil pemeriksaan kesiapan produksi (RULE-048). `blocking`: bila gagal, jangan go-live.
 * Bukan blocking = peringatan saja. `hint` tidak pernah memuat nilai rahasia (RULE-040, RULE-043).
 */
final readonly class PreflightCheck
{
    public function __construct(
        public string $id,
        public string $label,
        public bool $passed,
        public string $hint,
        public bool $blocking = true,
    ) {}
}
