<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;

final class RegisterUser
{
    public function __construct(private readonly SendVerificationEmail $sendVerificationEmail)
    {
    }

    /**
     * @param  array{name: string, email: string, password: string, institution_name?: string|null, major?: string|null}  $data
     * @return array{user: User, email_sent: bool}
     */
    public function handle(array $data): array
    {
        $user = new User($data);
        $user->role = UserRole::User;
        $user->is_active = true;
        $user->save();

        return [
            'user' => $user,
            'email_sent' => $this->sendVerificationEmail->handle($user),
        ];
    }
}
