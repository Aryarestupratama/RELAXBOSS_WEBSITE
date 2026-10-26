<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Account\ChangeUserPassword;
use App\Actions\Account\UpdateUserProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\ChangePasswordRequest;
use App\Http\Requests\App\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;

/** SCR-018: ubah profil (API-020) dan ganti kata sandi (API-021). Selalu milik pengguna yang masuk. */
final class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateUserProfile $action): RedirectResponse
    {
        $action->handle($request->user(), $request->profile());

        return back()->with('status', 'profile-updated');
    }

    public function updatePassword(ChangePasswordRequest $request, ChangeUserPassword $action): RedirectResponse
    {
        $action->handle(
            $request->user(),
            (string) $request->validated('current_password'),
            (string) $request->validated('password'),
            $request->session()->getId(),
        );

        return back()->with('status', 'password-updated');
    }
}
