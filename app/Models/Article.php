<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-012. Artikel edukasi untuk halaman Publik (SEO). Konten publik, jadi tidak dienkripsi
 * dan tidak punya relasi ke `users`: penulis hanyalah teks (`author_name`).
 * `published_at` disimpan UTC (RULE-035).
 * `related_assessment_slug` merujuk slug Instrumen tanpa foreign key (slug Asesmen tetap sejak dibuat).
 */
class Article extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'meta_title',
        'meta_description',
        'body_markdown',
        'category',
        'related_assessment_slug',
        'author_name',
        'status',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ArticleCategory::class,
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Artikel yang boleh tampil di halaman Publik: berstatus terbit, tanggal terbit sudah lewat,
     * dan di produksi bukan artikel contoh berslug `demo-` (RULE-008, RULE-048).
     *
     * @param  Builder<Article>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if (app()->isProduction()) {
            $query->where('slug', 'not like', 'demo-%');
        }
    }

    /** Artikel contoh (dummy) berslug `demo-`. */
    public function isDemo(): bool
    {
        return str_starts_with($this->slug, 'demo-');
    }

    /** @return BelongsTo<Assessment, $this> */
    public function relatedAssessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'related_assessment_slug', 'slug');
    }
}
