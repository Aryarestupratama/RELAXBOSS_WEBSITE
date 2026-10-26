<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\User;

/** API-020 (FR-033): ubah nama, jurusan, dan kampus milik sendiri. Email tidak bisa diubah di sini. */
final class UpdateUserProfile
{
    /**
     * @param  array{name: string, major: ?string, institution_name: ?string}  $data
     */
    public function handle(User $user, array $data): void
    {
        $user->forceFill([
            'name' => $data['name'],
            'major' => $data['major'],
            'institution_name' => $data['institution_name'],
        ])->save();
    }
}
