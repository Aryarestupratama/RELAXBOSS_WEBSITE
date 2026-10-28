<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\ArticleStatus;
use App\Models\Announcement;

/**
 * Membuat atau mengubah pengumuman. Berstatus terbit tanpa tanggal langsung terbit sekarang;
 * tanggal di masa depan menjadikannya terjadwal (hanya terbaca setelah tanggalnya).
 */
final class SaveAnnouncement
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(?Announcement $announcement, array $data): Announcement
    {
        $announcement ??= new Announcement;

        $announcement->fill($data);

        if ($announcement->status === ArticleStatus::Published && $announcement->published_at === null) {
            $announcement->published_at = now();
        }

        $announcement->save();

        return $announcement;
    }
}
