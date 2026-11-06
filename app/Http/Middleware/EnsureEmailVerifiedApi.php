<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Varian API dari `verified` (Architecture-M bagian 7): 403 `unverified` dalam amplop galat seragam.
 * Middleware bawaan `verified` mengembalikan 403 generik yang terbaca klien sebagai `forbidden`.
 */
final class EnsureEmailVerifiedApi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasVerifiedEmail()) {
            return ApiError::response(
                'unverified',
                'Verifikasi emailmu dulu, ya. Cek kotak masukmu.',
                403,
            );
        }

        return $next($request);
    }
}
