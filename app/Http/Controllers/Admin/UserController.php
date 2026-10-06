<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SetUserActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\User;
use App\Services\Admin\UserAdminPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-021 (FR-024, API-019): daftar Pengguna terbatas dan nonaktifkan akun.
 * Tidak ada hapus, ubah profil, atau halaman detail: admin tidak melihat data pribadi (RULE-042).
 */
final class UserController extends Controller
{
    public function index(Request $request, UserAdminPresenter $presenter): Response
    {
        $search = $request->query('q');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        $users = $presenter->paginate($search);

        return Inertia::render('Admin/Pengguna/Index', [
            'users' => $presenter->rows($users, $request->user()),
            'search' => $search,
            'prev_page_url' => $users->previousPageUrl(),
            'next_page_url' => $users->nextPageUrl(),
            'status' => session('status'),
        ]);
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user, SetUserActiveStatus $action): RedirectResponse
    {
        $active = $request->isActive();

        $action->handle($request->user(), $user, $active);

        return back()->with('status', $active ? 'user-activated' : 'user-deactivated');
    }
}
