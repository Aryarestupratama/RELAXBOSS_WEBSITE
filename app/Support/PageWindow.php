<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Daftar nomor halaman untuk paginasi bernomor: halaman pertama, terakhir, dan sekitar halaman aktif.
 * Celah satu halaman diisi nomornya; celah lebih besar menjadi `null` (titik-titik).
 */
final class PageWindow
{
    /**
     * @return list<int|null>
     */
    public static function make(int $current, int $last, int $onEachSide = 1): array
    {
        if ($last <= 1) {
            return [];
        }

        $current = min(max($current, 1), $last);

        $pages = range(max(1, $current - $onEachSide), min($last, $current + $onEachSide));
        $pages[] = 1;
        $pages[] = $last;
        $pages = array_values(array_unique($pages));
        sort($pages);

        $items = [];
        $previous = null;

        foreach ($pages as $page) {
            if ($previous !== null) {
                $gap = $page - $previous;

                if ($gap === 2) {
                    $items[] = $previous + 1;
                } elseif ($gap > 2) {
                    $items[] = null;
                }
            }

            $items[] = $page;
            $previous = $page;
        }

        return $items;
    }
}
