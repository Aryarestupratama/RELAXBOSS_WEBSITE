<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memutus sesi akun yang dinonaktifkan (Architecture bagian 7).
 * Tidak dipasang pada /keluar agar akun nonaktif tetap bisa keluar.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json([
                    'error' => [
                        'code' => 'account_inactive',
                        'message' => 'Akun Anda dinonaktifkan.',
                    ],
                ], 403);
            }

            return redirect('/masuk');
        }

        return $next($request);
    }
}
