<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\User;
use App\Support\Wib;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Daftar Pengguna terbatas untuk admin (FR-024, Schema bagian 4).
 *
 * Hanya email, tanggal daftar, status verifikasi, status aktif, dan peran. Sengaja tidak memuat
 * nama, jurusan, kampus, kolom consent, atau relasi ke data pribadi (RULE-042).
 */
final class UserAdminPresenter
{
    private const COLUMNS = ['id', 'email', 'role', 'is_active', 'email_verified_at', 'created_at'];

    /** Cari email (sebagian). Karakter khusus LIKE di-escape agar `%` dan `_` dibaca apa adanya. */
    public function paginate(?string $search): Paginator
    {
        $query = User::query()->select(self::COLUMNS);

        $term = $search === null ? '' : trim($search);

        if ($term !== '') {
            // ESCAPE '!' berlaku sama di SQLite dan MySQL (backslash bukan escape bawaan SQLite).
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $query->whereRaw("email like ? escape '!'", ["%{$escaped}%"]);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->simplePaginate((int) config('relaxboss.admin.users_per_page'))
            ->withQueryString();
    }

    /**
     * @return list<array{id: int, email: string, is_admin: bool, is_active: bool, is_verified: bool, registered_on: string, is_self: bool}>
     */
    public function rows(Paginator $users, User $actor): array
    {
        return collect($users->items())
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->isAdmin(),
                'is_active' => $user->is_active,
                'is_verified' => $user->email_verified_at !== null,
                'registered_on' => Wib::date($user->created_at),
                'is_self' => $user->is($actor),
            ])
            ->values()
            ->all();
    }
}
