<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Response;

/**
 * API-020 (sitemap.xml) dan API-021 (robots.txt).
 */
class SeoController extends Controller
{
    /** Halaman Publik tetap. Info Asesmen (daftar dan tiap detail) ditambahkan dinamis di `sitemap()`. */
    private const SITEMAP_PATHS = ['/', '/asesmen', '/tentang', '/konsultasi', '/privasi', '/ketentuan'];

    public function sitemap(): Response
    {
        $paths = self::SITEMAP_PATHS;

        $slugs = Assessment::query()
            ->publiclyListed()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('slug');

        foreach ($slugs as $slug) {
            $paths[] = '/asesmen/'.$slug;
        }

        $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($paths as $path) {
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
