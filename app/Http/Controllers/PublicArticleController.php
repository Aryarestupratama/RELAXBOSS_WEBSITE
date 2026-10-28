<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleCategory;
use App\Http\Requests\PublicArticleIndexRequest;
use App\Models\Article;
use App\Services\Article\ArticleSearch;
use App\Services\Article\PublicArticlePresenter;
use Illuminate\Contracts\View\View;

/**
 * Artikel edukasi Publik (Blade, dirender di server). FR-021, SCR-024, SCR-025.
 * Hanya artikel `published()` yang boleh tampil; draf, terjadwal, dan tidak ada menghasilkan 404.
 */
class PublicArticleController extends Controller
{
    public function index(PublicArticleIndexRequest $request, PublicArticlePresenter $presenter, ArticleSearch $search): View
    {
        $category = $request->category();
        $term = $request->searchTerm();

        $articles = Article::query()
            ->published()
            ->when($category !== null, fn ($query) => $query->where('category', $category?->value))
            ->when($term !== '', fn ($query) => $search->apply($query, $term))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate((int) config('relaxboss.limits.articles_per_page'))
            ->withQueryString();

        // Pil kategori hanya untuk kategori yang punya artikel terbit.
        $available = Article::query()->published()->distinct()->pluck('category')
            ->map(fn (mixed $value): ?ArticleCategory => $value instanceof ArticleCategory ? $value : ArticleCategory::tryFrom((string) $value))
            ->filter()
            ->unique()
            ->values();

        $categories = collect(ArticleCategory::cases())
            ->filter(fn (ArticleCategory $case): bool => $available->contains($case))
            ->map(fn (ArticleCategory $case): array => ['value' => $case->value, 'label' => $case->label()])
            ->values();

        return view('public.artikel-index', [
            'articles' => $articles->through(fn (Article $article): array => $presenter->listItem($article)),
            'categories' => $categories,
            'activeCategory' => $category?->value,
            'activeCategoryLabel' => $category?->label(),
            'searchTerm' => $term,
        ]);
    }

    public function show(string $slug, PublicArticlePresenter $presenter): View
    {
        $article = Article::query()->published()->where('slug', $slug)->firstOrFail();

        $related = Article::query()
            ->published()
            ->whereKeyNot($article->getKey())
            ->orderByRaw('category = ? desc', [$article->category->value])
            ->orderByDesc('published_at')
            ->limit((int) config('relaxboss.limits.articles_related'))
            ->get()
            ->map(fn (Article $item): array => $presenter->listItem($item));

        return view('public.artikel-detail', [
            'article' => $presenter->detail($article),
            'assessment' => $presenter->relatedAssessment($article),
            'related' => $related,
        ]);
    }
}
