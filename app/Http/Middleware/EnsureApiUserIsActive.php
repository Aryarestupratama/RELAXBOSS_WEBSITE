<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Varian API dari `active` (Architecture-M bagian 7). Middleware web `active` memutus sesi
 * (`session()->invalidate()`), yang tidak berlaku untuk token. Di sini semua token pengguna dicabut
 * lalu dijawab 403 `account_inactive` dalam amplop galat seragam; klien menghapus sesinya.
 * Dipasang setelah `auth:sanctum`. Tidak dipasang pada logout agar akun nonaktif tetap bisa keluar.
 */
final class EnsureApiUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            $user->tokens()->delete();

            return ApiError::response(
                'account_inactive',
                'Akunmu sedang dinonaktifkan. Hubungi kami bila menurutmu ini keliru.',
                403,
            );
        }

        return $next($request);
    }
}
