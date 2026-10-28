<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Models\NotificationPreference;
use App\Models\User;

/** FR-036: simpan pengaturan notifikasi milik sendiri. Baris dibuat saat pertama disimpan. */
final class UpdateNotificationPreferences
{
    public function handle(User $user, bool $moodReminderEnabled, int $moodReminderHour, bool $nudgesEnabled): void
    {
        $preference = $user->notificationPreference()->firstOrNew([], NotificationPreference::defaults());

        $preference->fill([
            'mood_reminder_enabled' => $moodReminderEnabled,
            'mood_reminder_hour' => $moodReminderHour,
            'nudges_enabled' => $nudgesEnabled,
        ]);
        $user->notificationPreference()->save($preference);
        $user->setRelation('notificationPreference', $preference);
    }
}
