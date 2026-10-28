<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Account\DeleteUserAccount;
use App\Actions\Account\ManageUserAvatar;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\DeleteAccountRequest;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/** SCR-018: info akun, persetujuan pelatihan (API-013 di controller lain), dan hapus akun (API-016). */
final class AccountController extends Controller
{
    public function show(Request $request, DeleteUserAccount $deleteAccount, ManageUserAvatar $avatar): Response
    {
        $user = $request->user();

        return Inertia::render('App/Akun/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'major' => $user->major,
                'institution_name' => $user->institution_name,
            ],
            'avatar_url' => $avatar->exists($user)
                ? '/app/akun/foto?v='.$user->avatar_updated_at->getTimestamp()
                : null,
            'is_only_admin' => $deleteAccount->isOnlyAdmin($user),
            'notification_preferences' => $this->notificationPreferences($user),
        ]);
    }

    /**
     * Pengaturan notifikasi milik sendiri (FR-036). Tanpa baris tersimpan berarti semua mati.
     *
     * @return array{mood_reminder_enabled: bool, mood_reminder_hour: int, nudges_enabled: bool, hour_min: int, hour_max: int}
     */
    private function notificationPreferences(User $user): array
    {
        $preference = $user->notificationPreference ?? new NotificationPreference(NotificationPreference::defaults());

        return [
            'mood_reminder_enabled' => $preference->mood_reminder_enabled,
            'mood_reminder_hour' => $preference->mood_reminder_hour,
            'nudges_enabled' => $preference->nudges_enabled,
            'hour_min' => (int) config('relaxboss.notifications.mood_hour_min'),
            'hour_max' => (int) config('relaxboss.notifications.mood_hour_max'),
        ];
    }

    /**
     * API-016. Setelah akun terhapus, sesi diakhiri dan pengguna dibawa ke Beranda
     * lewat kunjungan penuh (Beranda adalah halaman Blade), sama seperti logout.
     */
    public function destroy(DeleteAccountRequest $request, DeleteUserAccount $deleteAccount): SymfonyResponse
    {
        $deleteAccount->handle($request->user(), (string) $request->validated('password'));

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location('/');
    }
}
