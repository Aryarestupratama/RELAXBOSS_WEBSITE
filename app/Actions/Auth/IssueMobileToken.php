<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

/**
 * Menerbitkan token Sanctum untuk aplikasi mobile (Architecture-M bagian 7).
 * Satu token per perangkat dengan nama yang sama: token lama dicabut agar tidak menumpuk.
 * Token mentah hanya ada di hasil ini; di database hanya hash-nya (RULE-040).
 */
final class IssueMobileToken
{
    public function handle(User $user): NewAccessToken
    {
        $name = (string) config('relaxboss.mobile.token_name');

        $user->tokens()->where('name', $name)->delete();

        return $user->createToken(
            $name,
            (array) config('relaxboss.mobile.token_abilities'),
            now()->addDays((int) config('relaxboss.mobile.token_expiration_days')),
        );
    }
}
