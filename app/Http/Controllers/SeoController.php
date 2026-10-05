<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * API-020 (sitemap.xml) dan API-021 (robots.txt).
 */
class SeoController extends Controller
{
    /** Hanya halaman Publik. Info Asesmen ditambahkan di TASK-011. */
    private const SITEMAP_PATHS = ['/', '/tentang', '/konsultasi', '/privasi', '/ketentuan'];

    public function sitemap(): Response
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach (self::SITEMAP_PATHS as $path) {
            $lines[] = '  <url><loc>'.e(url($path)).'</loc></url>';
        }

        $lines[] = '</urlset>';

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /** Di luar produksi seluruh situs ditutup. Di produksi hanya halaman Publik yang terbuka. */
    public function robots(): Response
    {
        if (! app()->isProduction()) {
            $body = "User-agent: *\nDisallow: /\n";
        } else {
            $body = implode("\n", [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /app/',
                'Disallow: /dashboard',
                'Disallow: /daftar',
                'Disallow: /masuk',
                'Disallow: /lupa-sandi',
                'Disallow: /reset-sandi',
                'Disallow: /verifikasi-email',
                '',
                'Sitemap: '.url('/sitemap.xml'),
                '',
            ]);
        }

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
