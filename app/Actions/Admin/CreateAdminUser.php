<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Actions\Auth\SendPasswordResetLink;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * API-023 (FR-035): admin membuat akun admin baru. Tidak ada kata sandi yang diketik atau dikirim:
 * akun dibuat dengan sandi acak yang tidak diketahui siapa pun, lalu tautan atur sandi (token 60 menit,
 * sekali pakai) dikirim ke email itu. Admin baru memilih sandinya sendiri.
 */
final class CreateAdminUser
{
    public function __construct(private readonly SendPasswordResetLink $sendLink) {}

    public function handle(string $name, string $email): User
    {
        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Str::random(48), // di-hash oleh cast
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->sendLink->handle($email);

        return $user;
    }
}
