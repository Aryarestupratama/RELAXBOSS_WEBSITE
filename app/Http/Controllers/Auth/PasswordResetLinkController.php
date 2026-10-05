<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /** SCR-009 (form email) */
    public function create(): Response
    {
        return Inertia::render('Auth/LupaSandi', [
            'status' => session('status'),
            'expireMinutes' => (int) config('relaxboss.auth.reset_expire_minutes'),
        ]);
    }

    /** API-004: respons selalu sama. */
    public function store(ForgotPasswordRequest $request, SendPasswordResetLink $sendLink): RedirectResponse
    {
        $sendLink->handle((string) $request->validated('email'));

        return back()->with('status', 'reset-link-sent');
    }
}
