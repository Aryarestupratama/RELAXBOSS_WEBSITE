<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use App\Enums\NotificationKind;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Support\Wib;

/** Tutup satu nudge untuk hari ini (WIB). Hanya jenis yang boleh ditutup; menyimpan kunci enum dan tanggal, tanpa isi. */
final class DismissNudge
{
    public function handle(User $user, NotificationKind $kind): void
    {
        if (! $kind->isDismissible()) {
            return;
        }

        $preference = $user->notificationPreference()->firstOrNew([], NotificationPreference::defaults());

        $dismissals = $preference->nudge_dismissals ?? [];
        $dismissals[$kind->value] = Wib::date();

        $preference->nudge_dismissals = $dismissals;
        $user->notificationPreference()->save($preference);
        $user->setRelation('notificationPreference', $preference);
    }
}
