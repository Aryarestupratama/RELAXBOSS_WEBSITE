<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Skala mood 1 sampai 5 (ENT-006). Nilai disimpan sebagai angka; label dipetakan di sini.
 * Label mengikuti Design.md (SCR-016).
 */
enum MoodLevel: int
{
    case VerySad = 1;
    case Sad = 2;
    case Neutral = 3;
    case Happy = 4;
    case Great = 5;

    public function label(): string
    {
        return match ($this) {
            self::VerySad => 'Sangat sedih',
            self::Sad => 'Sedih',
            self::Neutral => 'Biasa',
            self::Happy => 'Senang',
            self::Great => 'Luar biasa',
        };
    }

    /** Kunci snake_case yang tercatat di Schema ENT-006. */
    public function key(): string
    {
        return match ($this) {
            self::VerySad => 'sangat_sedih',
            self::Sad => 'sedih',
            self::Neutral => 'biasa',
            self::Happy => 'senang',
            self::Great => 'luar_biasa',
        };
    }
}
