<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

/**
 * Halaman Publik (Blade, dirender di server). FR-020, FR-022.
 */
class PublicPageController extends Controller
{
    /** SCR-001 */
    public function home(): View
    {
        return view('public.beranda', [
            'umb' => (string) config('relaxboss.umb'),
        ]);
    }

    /** SCR-004 */
    public function about(): View
    {
        return view('public.tentang', [
            'umb' => (string) config('relaxboss.umb'),
        ]);
    }

    /** SCR-005. Kontak hanya dari config (OQ-3), tidak pernah ditulis di view. */
    public function consultation(): View
    {
        return view('public.konsultasi', [
            'contacts' => (array) config('relaxboss.crisis_contacts'),
        ]);
    }

    /** SCR-006 */
    public function privacy(): View
    {
        return view('public.privasi', [
            'backupDays' => 14,
        ]);
    }

    /** SCR-006 */
    public function terms(): View
    {
        return view('public.ketentuan');
    }
}
