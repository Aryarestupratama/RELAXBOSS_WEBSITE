<?php

declare(strict_types=1);

namespace App\Enums;

/** Status artikel edukasi (ENT-012). Hanya `published` yang boleh tampil di halaman Publik. */
enum ArticleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Published => 'Terbit',
        };
    }
}
