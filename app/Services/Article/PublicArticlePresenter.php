<?php

declare(strict_types=1);

namespace App\Services\Article;

use App\Models\Article;
use App\Models\Assessment;
use App\Support\Wib;
use Illuminate\Support\Str;

/**
 * Menyusun data Artikel untuk halaman Publik (SCR-024, SCR-025). Tanggal selalu dalam WIB (RULE-035).
 */
final class PublicArticlePresenter
{
    public function __construct(private readonly ArticleRenderer $renderer) {}

    /**
     * @return array<string, mixed>
     */
    public function listItem(Article $article): array
    {
        $published = Wib::toWib($article->published_at);

        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'category' => $article->category->value,
            'category_label' => $article->category->label(),
            'published_label' => $published->locale('id')->translatedFormat('j F Y'),
            'published_iso' => $published->toDateString(),
            'is_demo' => $article->isDemo(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Article $article): array
    {
        $published = Wib::toWib($article->published_at);
        $updated = Wib::toWib($article->updated_at);
        $wasUpdated = $updated->toDateString() > $published->toDateString();

        return [
            ...$this->listItem($article),
            'html' => $this->renderer->html($article->body_markdown),
            'reading_minutes' => $this->renderer->readingMinutes($article->body_markdown),
            'author' => $article->author_name,
            'updated_label' => $wasUpdated ? $updated->locale('id')->translatedFormat('j F Y') : null,
            'published_atom' => $published->toAtomString(),
            'updated_atom' => $updated->toAtomString(),
            'seo_title' => $article->meta_title ?: Str::limit($article->title, 46, '…').' | RelaxBoss',
            'seo_description' => $article->meta_description ?: Str::limit($article->excerpt, 155),
            'json_ld' => $this->jsonLd($article, $published->toAtomString(), $updated->toAtomString()),
        ];
    }

    /**
     * Asesmen terkait, hanya bila masih boleh tampil Publik (aktif, bukan `demo-` di produksi).
     *
     * @return array{slug: string, name: string}|null
     */
    public function relatedAssessment(Article $article): ?array
    {
        if ($article->related_assessment_slug === null) {
            return null;
        }

        $assessment = Assessment::query()
            ->publiclyListed()
            ->where('slug', $article->related_assessment_slug)
            ->first();

        return $assessment === null
            ? null
            : ['slug' => $assessment->slug, 'name' => (string) ($assessment->display_name ?? $assessment->name)];
    }

    /** Data terstruktur Article (schema.org). Dibuat di PHP karena Blade membaca `@context` sebagai directive. */
    private function jsonLd(Article $article, string $published, string $updated): string
    {
        return (string) json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->meta_description ?: Str::limit($article->excerpt, 155),
            'datePublished' => $published,
            'dateModified' => $updated,
            'author' => ['@type' => 'Person', 'name' => $article->author_name],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'RelaxBoss',
                'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-relaxboss.png')],
            ],
            'mainEntityOfPage' => url('/artikel/'.$article->slug),
            'inLanguage' => 'id',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }
}
