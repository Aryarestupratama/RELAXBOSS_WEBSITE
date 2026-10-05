<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

/**
 * API-004. Hasil tidak dikembalikan: respons ke pengguna selalu sama
 * (email terdaftar atau tidak, terkirim atau tidak) agar email tidak bisa ditebak.
 */
final class SendPasswordResetLink
{
    public function handle(string $email): void
    {
        try {
            Password::sendResetLink(['email' => $email]);
        } catch (Throwable $e) {
            Log::warning('password_reset_email_failed', ['exception' => $e::class]);
        }
    }
}
