<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Satu-satunya helper tanggal WIB (RULE-035).
 * Waktu disimpan UTC; konversi ke WIB hanya lewat kelas ini.
 */
final class Wib
{
    public const TIMEZONE = 'Asia/Jakarta';

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE);
    }

    public static function toWib(CarbonInterface $moment): CarbonImmutable
    {
        return CarbonImmutable::instance($moment)->setTimezone(self::TIMEZONE);
    }

    /** Tanggal WIB (Y-m-d) untuk $moment, atau sekarang bila null. Dipakai mis. `mood_entries.entry_date`. */
    public static function date(?CarbonInterface $moment = null): string
    {
        return ($moment === null ? self::now() : self::toWib($moment))->format('Y-m-d');
    }

    /** Awal hari WIB (00:00) dari $moment, atau hari ini bila null. */
    public static function startOfDay(?CarbonInterface $moment = null): CarbonImmutable
    {
        return ($moment === null ? self::now() : self::toWib($moment))->startOfDay();
    }
}
