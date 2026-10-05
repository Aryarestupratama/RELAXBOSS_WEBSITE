<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /** SCR-008 */
    public function create(): Response
    {
        return Inertia::render('Auth/Masuk', [
            'status' => session('status'),
        ]);
    }

    /** API-002 */
    public function store(LoginRequest $request, LoginUser $login): RedirectResponse
    {
        $login->handle(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
            (string) $request->ip(),
        );

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
