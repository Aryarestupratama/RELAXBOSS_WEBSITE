<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetUserPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /** SCR-009 (kolom sandi baru) */
    public function create(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetSandi', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'minPasswordLength' => (int) config('relaxboss.auth.password_min_length'),
        ]);
    }

    /** API-005: token salah, kedaluwarsa, dan email tidak dikenal digabung menjadi galat `reset`. */
    public function store(ResetPasswordRequest $request, ResetUserPassword $reset): RedirectResponse
    {
        $ok = $reset->handle([
            'email' => (string) $request->validated('email'),
            'token' => (string) $request->validated('token'),
            'password' => (string) $request->validated('password'),
        ]);

        if (! $ok) {
            return back()->withErrors([
                'reset' => 'Tautan reset ini tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        return redirect('/masuk')->with('status', 'password-reset');
    }
}
