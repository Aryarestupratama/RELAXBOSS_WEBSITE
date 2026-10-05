<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Kirim ulang verifikasi, maks. resend_per_hour kali per jam (FR-002).
 * Batas dikembalikan sebagai galat form `resend` (bukan 429; Architecture bagian 11).
 */
final class ResendVerificationEmail
{
    public function __construct(private readonly SendVerificationEmail $sendVerificationEmail)
    {
    }

    public function handle(User $user): bool
    {
        $key = 'verification-resend:'.$user->getKey();
        $max = (int) config('relaxboss.auth.resend_per_hour');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

            throw ValidationException::withMessages([
                'resend' => "Kamu sudah meminta terlalu banyak email. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        RateLimiter::hit($key, (int) config('relaxboss.auth.resend_window_seconds'));

        return $this->sendVerificationEmail->handle($user);
    }
}
