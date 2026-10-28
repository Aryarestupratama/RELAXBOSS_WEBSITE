<?php

declare(strict_types=1);

namespace App\Services\Article;

use App\Enums\ArticleCategory;
use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;

/**
 * Pencarian artikel Publik (FR-021): tiap kata harus cocok dengan judul atau nama kategori.
 * Memakai LIKE dengan karakter escape `!` karena SQLite (lokal) tidak punya escape bawaan.
 * Kata kunci tidak pernah dicatat di log (RULE-040).
 */
final class ArticleSearch
{
    /**
     * @param  Builder<Article>  $query
     */
    public function apply(Builder $query, string $term): void
    {
        foreach ($this->words($term) as $word) {
            $pattern = '%'.$this->escapeLike($word).'%';
            $categories = $this->categoriesMatching($word);

            $query->where(function (Builder $inner) use ($pattern, $categories): void {
                $inner->whereRaw("title like ? escape '!'", [$pattern]);

                if ($categories !== []) {
                    $inner->orWhereIn('category', $categories);
                }
            });
        }
    }

    /**
     * @return list<string>
     */
    private function words(string $term): array
    {
        $words = [];

        foreach (explode(' ', $term) as $word) {
            if ($word !== '' && ! in_array(mb_strtolower($word), array_map('mb_strtolower', $words), true)) {
                $words[] = $word;
            }
        }

        return array_slice($words, 0, (int) config('relaxboss.limits.articles_search_max_terms'));
    }

    /**
     * Kategori yang nama atau nilainya memuat kata tersebut.
     *
     * @return list<string>
     */
    private function categoriesMatching(string $word): array
    {
        $matches = [];

        foreach (ArticleCategory::cases() as $case) {
            if (mb_stripos($case->label(), $word) !== false || mb_stripos($case->value, $word) !== false) {
                $matches[] = $case->value;
            }
        }

        return $matches;
    }

    private function escapeLike(string $word): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word);
    }
}
