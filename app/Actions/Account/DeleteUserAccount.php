<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * API-016 (FR-019): hapus akun dan seluruh data pribadi setelah konfirmasi kata sandi.
 *
 * Satu transaksi. `DELETE users` memicu cascade FK ke sessions, assessment_attempts, mood_entries,
 * conversations, dan messages (Schema bagian 5). `crisis_events`, `ai_usage_daily`, dan
 * `admin_access_logs` tidak punya identitas pengguna sehingga tidak terpengaruh.
 *
 * Galat dikembalikan sebagai galat form (bukan 409/429), sejalan dengan pola Auth dan Mood:
 * `password` (sandi salah atau terlalu banyak percobaan) dan `account` (admin terakhir).
 */
final class DeleteUserAccount
{
    /** Pengguna adalah admin terakhir: akun ini tidak boleh dihapus (API-016). */
    public function isOnlyAdmin(User $user): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return ! User::query()
            ->where('role', UserRole::Admin->value)
            ->whereKeyNot($user->getKey())
            ->exists();
    }

    /**
     * @throws ValidationException
     */
    public function handle(User $user, string $password): void
    {
        $this->assertNotOnlyAdmin($user);

        $key = 'delete-account:'.$user->getKey();
        $max = (int) config('relaxboss.auth.delete_account_per_minute');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'password' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'password' => 'Kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($key);

        DB::transaction(function () use ($user): void {
            if ($user->isAdmin()) {
                // Kunci baris admin agar dua admin yang menghapus bersamaan tidak menyisakan nol admin.
                User::query()->where('role', UserRole::Admin->value)->lockForUpdate()->pluck('id');
            }

            $this->assertNotOnlyAdmin($user);

            // Sengaja hapus lewat query, bukan $user->delete(): model tetap `exists = true`
            // sehingga logout (yang boleh menyimpan remember_token) tidak membuat ulang barisnya.
            User::query()->whereKey($user->getKey())->delete();
        });
    }

    /**
     * @throws ValidationException
     */
    private function assertNotOnlyAdmin(User $user): void
    {
        if ($this->isOnlyAdmin($user)) {
            throw ValidationException::withMessages([
                'account' => 'Kamu satu-satunya admin, jadi akun ini belum bisa dihapus. Jadikan orang lain admin lebih dulu.',
            ]);
        }
    }
}
