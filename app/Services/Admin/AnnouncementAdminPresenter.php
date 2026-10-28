<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\ArticleStatus;
use App\Models\Announcement;
use App\Support\Wib;
use Illuminate\Contracts\Pagination\Paginator;

/** Menyusun data Admin Pengumuman (SCR-028). Konten global; tidak ada data pengguna. */
final class AnnouncementAdminPresenter
{
    public function paginate(): Paginator
    {
        return Announcement::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->simplePaginate((int) config('relaxboss.admin.announcements.per_page'));
    }

    /**
     * @param  iterable<Announcement>  $announcements
     * @return list<array<string, mixed>>
     */
    public function rows(iterable $announcements): array
    {
        $rows = [];

        foreach ($announcements as $announcement) {
            $rows[] = [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'state' => $this->state($announcement),
                'published_label' => $announcement->published_at === null
                    ? null
                    : Wib::toWib($announcement->published_at)->locale('id')->translatedFormat('j M Y H:i'),
                'updated_label' => Wib::toWib($announcement->updated_at)->locale('id')->translatedFormat('j M Y H:i'),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function form(?Announcement $announcement): array
    {
        return [
            'mode' => $announcement === null ? 'create' : 'edit',
            'id' => $announcement?->id,
            'version' => $announcement === null ? 'new' : (string) $announcement->updated_at?->getTimestamp(),
            'values' => [
                'title' => $announcement->title ?? '',
                'body_markdown' => $announcement->body_markdown ?? '',
                'status' => $announcement === null ? ArticleStatus::Draft->value : $announcement->status->value,
                'published_at' => $announcement?->published_at === null
                    ? ''
                    : Wib::toWib($announcement->published_at)->format('Y-m-d\TH:i'),
            ],
            'statuses' => array_map(
                static fn (ArticleStatus $case): array => ['value' => $case->value, 'label' => $case->label()],
                ArticleStatus::cases(),
            ),
            'body_max_chars' => (int) config('relaxboss.admin.announcements.body_max_chars'),
        ];
    }

    /** `draft`, `scheduled` (terbit tetapi tanggalnya belum tiba), atau `live` (terbaca pengguna). */
    private function state(Announcement $announcement): string
    {
        if ($announcement->status !== ArticleStatus::Published) {
            return 'draft';
        }

        return $announcement->published_at !== null && $announcement->published_at->lte(now()) ? 'live' : 'scheduled';
    }
}
