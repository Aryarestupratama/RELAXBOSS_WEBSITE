<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResendVerificationEmail;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    /** SCR-010 */
    public function notice(Request $request): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }

        return Inertia::render('Auth/VerifikasiEmail', [
            'email' => $user->email,
            'expireMinutes' => (int) config('relaxboss.auth.verification_expire_minutes'),
            'resendPerHour' => (int) config('relaxboss.auth.resend_per_hour'),
            'status' => session('status'),
        ]);
    }

    /** API-006: tautan bertanda tangan; id/hash tidak cocok = 403. */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect('/dashboard');
    }

    /** API-007 */
    public function send(Request $request, ResendVerificationEmail $resend): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }

        $sent = $resend->handle($user);

        return back()->with('status', $sent ? 'verification-link-sent' : 'verification-send-failed');
    }
}
