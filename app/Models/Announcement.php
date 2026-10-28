<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * ENT-014. Pengumuman pembaruan website yang ditulis admin dan dibaca semua pengguna di lonceng (FR-037).
 * Konten global: tidak punya relasi ke `users` dan tidak dienkripsi. `published_at` disimpan UTC (RULE-035).
 * Memakai enum `ArticleStatus` (draft atau published).
 */
class Announcement extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'body_markdown',
        'status',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Pengumuman yang boleh dibaca pengguna: berstatus terbit dan tanggal terbitnya sudah lewat.
     *
     * @param  Builder<Announcement>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
