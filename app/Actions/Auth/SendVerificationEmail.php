<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Kirim email verifikasi (sinkron). Kegagalan kirim tidak melempar galat:
 * dicatat tanpa email (RULE-040) dan dikembalikan sebagai false.
 */
final class SendVerificationEmail
{
    public function handle(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $e) {
            Log::warning('verification_email_failed', [
                'user_id' => $user->getKey(),
                'exception' => $e::class,
            ]);

            return false;
        }
    }
}
