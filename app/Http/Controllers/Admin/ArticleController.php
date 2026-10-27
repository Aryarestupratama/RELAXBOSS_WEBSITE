<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveArticle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PreviewArticleRequest;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Services\Admin\ArticleAdminPresenter;
use App\Services\Article\ArticleRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-026 (FR-021, API-022 sampai API-024): kelola artikel edukasi. Konten publik tanpa data pengguna.
 * Tanpa hapus: artikel dikembalikan ke draf bila tidak ingin tampil (URL dan SEO tidak rusak tanpa sengaja).
 */
final class ArticleController extends Controller
{
    public function index(ArticleAdminPresenter $presenter): Response
    {
        $articles = $presenter->paginate();

        return Inertia::render('Admin/Artikel/Index', [
            'articles' => $presenter->rows($articles),
            'prev_page_url' => $articles->previousPageUrl(),
            'next_page_url' => $articles->nextPageUrl(),
        ]);
    }

    public function create(ArticleAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Artikel/Form', [
            'form' => $presenter->form(null),
        ]);
    }

    public function store(StoreArticleRequest $request, SaveArticle $action): RedirectResponse
    {
        $article = $action->handle(null, $request->article(), $request->slug());

        return redirect()->route('admin.articles.edit', $article)->with('status', 'article-created');
    }

    public function edit(Article $article, ArticleAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Artikel/Form', [
            'form' => $presenter->form($article),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article, SaveArticle $action): RedirectResponse
    {
        $action->handle($article, $request->article());

        return redirect()->route('admin.articles.edit', $article)->with('status', 'article-updated');
    }

    /** API-024: pratinjau Markdown dengan renderer yang sama dengan halaman Publik. */
    public function preview(PreviewArticleRequest $request, ArticleRenderer $renderer): JsonResponse
    {
        return response()->json([
            'data' => ['html' => $renderer->html((string) $request->validated('body_markdown'))],
        ]);
    }
}
