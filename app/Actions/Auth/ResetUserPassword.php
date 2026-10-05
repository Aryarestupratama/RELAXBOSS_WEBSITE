<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * API-005. Token 60 menit dan sekali pakai (password broker Laravel).
 * Setelah berhasil: ganti remember_token dan hapus semua sesi pengguna.
 */
final class ResetUserPassword
{
    /**
     * @param  array{email: string, token: string, password: string}  $credentials
     */
    public function handle(array $credentials): bool
    {
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->password = $password; // di-hash oleh cast
            $user->setRememberToken(Str::random(60));
            $user->save();

            DB::table('sessions')->where('user_id', $user->getKey())->delete();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET;
    }
}
