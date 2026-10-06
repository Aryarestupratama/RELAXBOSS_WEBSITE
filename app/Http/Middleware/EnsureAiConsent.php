<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menahan fitur AI (API-010, API-014, API-015) sampai pengguna menyelesaikan consent:
 * consent AI versi terbaru dan pilihan persetujuan pelatihan (FR-013).
 *
 * Dipasang pada aksi AI, bukan pada halaman RelaxMate (GET): halaman itu harus terbuka
 * agar `ConsentDialog` bisa tampil (SCR-017).
 */
final class EnsureAiConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->hasCompletedAiConsent()) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'error' => [
                    'code' => 'consent_required',
                    'message' => 'Kamu perlu menyetujui penggunaan AI terlebih dulu.',
                ],
            ], 403);
        }

        // Design SCR-017: menolak consent mengembalikan pengguna ke Dashboard.
        return redirect('/dashboard');
    }
}
