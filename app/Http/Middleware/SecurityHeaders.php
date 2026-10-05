<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan global (Architecture bagian 8 dan 11).
 * Global, bukan grup `web`, agar halaman 404 ikut. HSTS diatur di hosting.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Harus sebelum view dirender agar @vite memakai nonce yang sama.
        $nonce = Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', $this->policy($nonce));

        return $response;
    }

    private function policy(string $nonce): string
    {
        $script = ["'self'", "'nonce-{$nonce}'"];
        $style = ["'self'", "'nonce-{$nonce}'"];
        $font = ["'self'", 'data:'];
        $img = ["'self'", 'data:'];
        $connect = ["'self'"];

        if (app()->environment('local') && is_file(public_path('hot'))) {
            // CSP tidak bisa mencocokkan alamat IPv6 literal (http://[::1]:5173), jadi di
            // lokal dengan dev server Vite dipakai sumber skema. Tidak berlaku di produksi.
            $script[] = 'http:';
            $font[] = 'http:';
            $img[] = 'http:';
            $connect[] = 'http:';
            $connect[] = 'ws:';
            // Vite dev menyuntik <style> tanpa nonce; nonce dibuang agar 'unsafe-inline' berlaku.
            $style = ["'self'", "'unsafe-inline'", 'http:'];
        }

        return implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', $script),
            'style-src '.implode(' ', $style),
            "style-src-attr 'unsafe-inline'",
            'img-src '.implode(' ', $img),
            'font-src '.implode(' ', $font),
            'connect-src '.implode(' ', $connect),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}
