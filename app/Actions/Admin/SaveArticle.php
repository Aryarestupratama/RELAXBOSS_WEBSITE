<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\ArticleStatus;
use App\Models\Article;

/**
 * Membuat atau mengubah artikel. Slug hanya diisi saat membuat. Artikel berstatus terbit tanpa tanggal
 * langsung terbit sekarang; tanggal di masa depan menjadikannya terjadwal (hanya tampil setelah tanggalnya).
 */
final class SaveArticle
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?Article $article, array $data, ?string $slug = null): Article
    {
        $article ??= new Article;

        if ($slug !== null) {
            $article->slug = $slug;
        }

        $article->fill($data);

        if ($article->status === ArticleStatus::Published && $article->published_at === null) {
            $article->published_at = now();
        }

        $article->save();

        return $article;
    }
}
