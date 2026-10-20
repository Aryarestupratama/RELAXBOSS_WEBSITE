<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Kategori artikel edukasi (ENT-012). Daftar awal ini SEMENTARA dan ditentukan founder
 * (isi konten, bukan keputusan teknis). Menambah atau mengganti kategori cukup mengubah enum
 * ini; tanpa migration karena kolom `category` berupa string.
 */
enum ArticleCategory: string
{
    case Stress = 'stres';
    case Sleep = 'tidur';
    case Anxiety = 'kecemasan';
    case Burnout = 'burnout';

    public function label(): string
    {
        return match ($this) {
            self::Stress => 'Stres',
            self::Sleep => 'Tidur',
            self::Anxiety => 'Kecemasan',
            self::Burnout => 'Burnout',
        };
    }
}
