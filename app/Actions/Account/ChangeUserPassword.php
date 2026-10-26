<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * API-021 (FR-034): ganti kata sandi setelah memasukkan kata sandi saat ini.
 *
 * Galat dikembalikan sebagai galat form `current_password`. Setelah berhasil, sesi perangkat lain
 * diakhiri (sesi yang sedang dipakai tetap hidup) dan remember_token diganti.
 */
final class ChangeUserPassword
{
    /**
     * @throws ValidationException
     */
    public function handle(User $user, string $current, string $new, string $currentSessionId): void
    {
        $key = 'change-password:'.$user->getKey();
        $max = (int) config('relaxboss.auth.change_password_per_minute');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'current_password' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Hash::check($current, $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini tidak cocok.',
            ]);
        }

        RateLimiter::clear($key);

        DB::transaction(function () use ($user, $new, $currentSessionId): void {
            $user->password = $new; // di-hash oleh cast
            $user->setRememberToken(Str::random(60));
            $user->save();

            DB::table('sessions')
                ->where('user_id', $user->getKey())
                ->where('id', '!=', $currentSessionId)
                ->delete();
        });
    }
}
