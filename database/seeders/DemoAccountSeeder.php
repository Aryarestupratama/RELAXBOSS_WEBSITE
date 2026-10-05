<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun uji dummy (domain example.test, RULE-008). Hanya lokal.
 * Sandi semua akun: "password". Dua akun aktif dipakai uji isolasi data (TASK-029).
 */
class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoAccountSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        $accounts = [
            ['Demo Mahasiswa A', 'demo-a@example.test', 'Psikologi', true, true],
            ['Demo Mahasiswa B', 'demo-b@example.test', 'Teknik Informatika', true, true],
            ['Demo Belum Verifikasi', 'demo-unverified@example.test', null, false, true],
            ['Demo Nonaktif', 'demo-inactive@example.test', 'Manajemen', true, false],
        ];

        foreach ($accounts as [$name, $email, $major, $verified, $active]) {
            $user = User::query()->firstOrNew(['email' => $email]);

            $user->name = $name;
            $user->password = 'password';
            $user->institution_name = 'Kampus Demo';
            $user->major = $major;
            $user->role = UserRole::User;
            $user->is_active = $active;
            $user->email_verified_at = $verified ? now() : null;
            $user->save();
        }
    }
}
