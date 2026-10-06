<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Account\DeleteUserAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\DeleteAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/** SCR-018: info akun, persetujuan pelatihan (API-013 di controller lain), dan hapus akun (API-016). */
final class AccountController extends Controller
{
    public function show(Request $request, DeleteUserAccount $deleteAccount): Response
    {
        $user = $request->user();

        return Inertia::render('App/Akun/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'major' => $user->major,
                'institution_name' => $user->institution_name,
            ],
            'is_only_admin' => $deleteAccount->isOnlyAdmin($user),
        ]);
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
