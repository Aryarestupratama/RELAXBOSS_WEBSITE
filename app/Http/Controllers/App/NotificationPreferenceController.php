<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Notification\UpdateNotificationPreferences;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\UpdateNotificationPreferencesRequest;
use Illuminate\Http\RedirectResponse;

/** SCR-018 (kartu Notifikasi): simpan pengaturan notifikasi milik sendiri (FR-036). */
final class NotificationPreferenceController extends Controller
{
    public function update(UpdateNotificationPreferencesRequest $request, UpdateNotificationPreferences $action): RedirectResponse
    {
        $action->handle(
            $request->user(),
            $request->boolean('mood_reminder_enabled'),
            (int) $request->validated('mood_reminder_hour'),
            $request->boolean('nudges_enabled'),
        );

        return back()->with('status', 'notifications-updated');
    }
}
