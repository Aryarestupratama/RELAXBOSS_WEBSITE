<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Penyamaran angka agregat untuk admin (RULE-042): kelompok 1 sampai (batas - 1) tampil "<5".
 * Nol tidak disamarkan karena tidak mengungkap siapa pun. Batas dari `relaxboss.admin.min_group_size` (RULE-026).
 */
final class Aggregate
{
    public static function minGroup(): int
    {
        return (int) config('relaxboss.admin.min_group_size');
    }

    public static function count(int $value): string
    {
        if ($value === 0) {
            return '0';
        }

        if ($value < self::minGroup()) {
            return '<'.self::minGroup();
        }

        return number_format($value, 0, ',', '.');
    }

    /** Persentase bulat, atau null bila penyebut terlalu kecil (kelompok disamarkan). */
    public static function percent(int $part, int $whole): ?string
    {
        if ($whole < self::minGroup()) {
            return null;
        }

        return (string) round($part / $whole * 100).'%';
    }
}
