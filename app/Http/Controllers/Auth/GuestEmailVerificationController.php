<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

/**
 * Verifikasi email lewat tautan bertanda tangan TANPA sesi login (OQ-MA2).
 * Dipakai email yang dipicu API mobile: pengguna membukanya di peramban HP yang tidak punya sesi.
 * Tautan web biasa tetap memakai `verification.verify` (wajib login).
 * Keamanan: tanda tangan (middleware `signed`) dan hash email yang cocok.
 */
final class GuestEmailVerificationController extends Controller
{
    public function verify(int $id, string $hash): RedirectResponse
    {
        $user = User::query()->find($id);

        if ($user === null || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('login')->with('status', 'email-verified');
    }
}
