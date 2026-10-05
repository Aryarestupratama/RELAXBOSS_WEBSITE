<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Daftar', [
            'minPasswordLength' => (int) config('relaxboss.auth.password_min_length'),
        ]);
    }

    public function store(RegisterRequest $request, RegisterUser $registerUser): RedirectResponse
    {
        /** @var array{name: string, email: string, password: string, institution_name?: string|null, major?: string|null} $data */
        $data = $request->validated();

        $result = $registerUser->handle($data);

        Auth::login($result['user']);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')->with(
            'status',
            $result['email_sent'] ? 'verification-link-sent' : 'verification-send-failed',
        );
    }
}
