<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\NotificationPreference;
use App\Models\User;

/** Tandai semua pengumuman yang sudah terbit sebagai dibaca: `announcements_seen_at` diisi sekarang (UTC, RULE-035). */
final class MarkAnnouncementsRead
{
    public function handle(User $user): void
    {
        $preference = $user->notificationPreference()->firstOrNew([], NotificationPreference::defaults());

        $preference->announcements_seen_at = now();
        $user->notificationPreference()->save($preference);
        $user->setRelation('notificationPreference', $preference);
    }
}
