<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * API-019 (FR-024): aktifkan atau nonaktifkan akun.
 *
 * Penolakan dikembalikan sebagai galat form `user_status` (bukan 403), sejalan dengan pola
 * Auth, Mood, dan Akun: respons 403 tanpa JSON tampil sebagai modal galat di Inertia.
 * Sisi server tetap menolak; UI hanya menyembunyikan tombolnya.
 *
 * Menonaktifkan juga menghapus baris `sessions` akun itu agar sesi aktifnya putus seketika
 * (middleware `active` tetap menjadi lapis kedua).
 */
final class SetUserActiveStatus
{
    /**
     * @throws ValidationException
     */
    public function handle(User $actor, User $target, bool $active): void
    {
        if (! $active) {
            $this->assertCanDeactivate($actor, $target);
        }

        DB::transaction(function () use ($actor, $target, $active): void {
            if (! $active && $target->isAdmin()) {
                // Kunci baris admin agar dua penonaktifan bersamaan tidak menyisakan nol admin aktif.
                User::query()->where('role', UserRole::Admin->value)->lockForUpdate()->pluck('id');
            }

            if (! $active) {
                $this->assertCanDeactivate($actor, $target);
            }

            User::query()->whereKey($target->getKey())->update(['is_active' => $active]);

            if (! $active) {
                DB::table('sessions')->where('user_id', $target->getKey())->delete();
            }
        });
    }

    /**
     * @throws ValidationException
     */
    private function assertCanDeactivate(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw ValidationException::withMessages([
                'user_status' => 'Kamu tidak bisa menonaktifkan akunmu sendiri.',
            ]);
        }

        if ($target->isAdmin() && ! $this->hasOtherActiveAdmin($target)) {
            throw ValidationException::withMessages([
                'user_status' => 'Ini satu-satunya admin aktif, jadi akunnya belum bisa dinonaktifkan.',
            ]);
        }
    }

    private function hasOtherActiveAdmin(User $target): bool
    {
        return User::query()
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->whereKeyNot($target->getKey())
            ->exists();
    }
}
