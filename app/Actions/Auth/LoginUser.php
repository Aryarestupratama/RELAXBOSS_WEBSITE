<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * API-002. Batas: login_per_minute percobaan GAGAL per kombinasi akun + IP.
 * Kunci memakai sha1 agar email tidak tersimpan di tabel cache.
 * Status nonaktif baru diungkap setelah sandi terbukti benar.
 */
final class LoginUser
{
    public function handle(string $email, string $password, string $ip): User
    {
        $key = sha1(mb_strtolower($email).'|'.$ip);
        $max = (int) config('relaxboss.auth.login_per_minute');
        $window = (int) config('relaxboss.auth.login_window_seconds');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            // Satu operasi hash tetap dijalankan agar waktu respons tidak membocorkan keberadaan email.
            Hash::make($password);
            $valid = false;
        } else {
            $valid = Hash::check($password, $user->password);
        }

        if (! $valid || $user === null) {
            RateLimiter::hit($key, $window);

            throw ValidationException::withMessages([
                'login' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($key);

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Akunmu sedang dinonaktifkan. Hubungi kami bila menurutmu ini keliru.',
            ]);
        }

        Auth::login($user);

        return $user;
    }
}
