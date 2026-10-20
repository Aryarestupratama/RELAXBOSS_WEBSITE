<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Assessment;
use App\Support\Wib;
use Illuminate\Contracts\Pagination\Paginator;

/** Menyusun data Admin Artikel (SCR-026). Konten publik; tidak ada data pengguna. */
final class ArticleAdminPresenter
{
    public function paginate(): Paginator
    {
        return Article::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->simplePaginate((int) config('relaxboss.admin.articles.per_page'));
    }

    /**
     * @param  iterable<Article>  $articles
     * @return list<array<string, mixed>>
     */
    public function rows(iterable $articles): array
    {
        $rows = [];

        foreach ($articles as $article) {
            $rows[] = [
                'id' => $article->id,
                'slug' => $article->slug,
                'title' => $article->title,
                'category_label' => $article->category->label(),
                'state' => $this->state($article),
                'is_demo' => $article->isDemo(),
                'published_label' => $article->published_at === null
                    ? null
                    : Wib::toWib($article->published_at)->locale('id')->translatedFormat('j M Y H:i'),
                'updated_label' => Wib::toWib($article->updated_at)->locale('id')->translatedFormat('j M Y H:i'),
                'public_url' => $this->state($article) === 'live' ? url('/artikel/'.$article->slug) : null,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function form(?Article $article): array
    {
        return [
            'mode' => $article === null ? 'create' : 'edit',
            'id' => $article?->id,
            'version' => $article === null ? 'new' : (string) $article->updated_at?->getTimestamp(),
            'is_demo' => $article?->isDemo() ?? false,
            'public_url' => $article !== null && $this->state($article) === 'live' ? url('/artikel/'.$article->slug) : null,
            'values' => [
                'slug' => $article->slug ?? '',
                'title' => $article->title ?? '',
                'excerpt' => $article->excerpt ?? '',
                'meta_title' => $article->meta_title ?? '',
                'meta_description' => $article->meta_description ?? '',
                'body_markdown' => $article->body_markdown ?? '',
                'category' => $article === null ? ArticleCategory::cases()[0]->value : $article->category->value,
                'related_assessment_slug' => $article->related_assessment_slug ?? '',
                'author_name' => $article->author_name ?? '',
                'status' => $article === null ? ArticleStatus::Draft->value : $article->status->value,
                'published_at' => $article?->published_at === null
                    ? ''
                    : Wib::toWib($article->published_at)->format('Y-m-d\TH:i'),
            ],
            'categories' => array_map(
                static fn (ArticleCategory $case): array => ['value' => $case->value, 'label' => $case->label()],
                ArticleCategory::cases(),
            ),
            'statuses' => array_map(
                static fn (ArticleStatus $case): array => ['value' => $case->value, 'label' => $case->label()],
                ArticleStatus::cases(),
            ),
            'assessments' => Assessment::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['slug', 'name', 'display_name'])
                ->map(static fn (Assessment $assessment): array => [
                    'slug' => $assessment->slug,
                    'name' => (string) ($assessment->display_name ?? $assessment->name),
                ])
                ->all(),
        ];
    }

    /** `draft`, `scheduled` (terbit tetapi tanggalnya belum tiba), atau `live` (tampil Publik). */
    private function state(Article $article): string
    {
        if ($article->status !== ArticleStatus::Published) {
            return 'draft';
        }

        return $article->published_at !== null && $article->published_at->lte(now()) ? 'live' : 'scheduled';
    }
}
