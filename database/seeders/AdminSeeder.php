<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Admin tunggal dari .env (ADMIN_SEED_EMAIL, ADMIN_SEED_PASSWORD).
 * Aman dijalankan ulang: tidak mengubah sandi admin yang sudah ada.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('relaxboss.admin_seed.email');
        $password = config('relaxboss.admin_seed.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || strlen($password) < 8) {
            throw new RuntimeException(
                'ADMIN_SEED_EMAIL dan ADMIN_SEED_PASSWORD (min. 8 karakter) wajib diisi di .env.'
            );
        }

        $admin = User::query()->firstOrNew(['email' => $email]);

        if (! $admin->exists) {
            $admin->name = 'Admin RelaxBoss';
            $admin->password = $password; // di-hash oleh cast
        }

        $admin->role = UserRole::Admin;
        $admin->is_active = true;
        $admin->email_verified_at ??= now();
        $admin->save();
    }
}
