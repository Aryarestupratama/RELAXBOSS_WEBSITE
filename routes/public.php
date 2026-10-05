<?php

declare(strict_types=1);

use App\Http\Controllers\PublicAssessmentController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Blade): FR-020, FR-022.
Route::get('/', [PublicPageController::class, 'home'])->name('home');
// Info Asesmen (SCR-002). Nama `assessments.*` dibedakan dari `app.assessments.*` di area aplikasi.
Route::get('/asesmen', [PublicAssessmentController::class, 'index'])->name('assessments.index');
Route::get('/asesmen/{slug}', [PublicAssessmentController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('assessments.show');
Route::get('/tentang', [PublicPageController::class, 'about'])->name('about');
Route::get('/konsultasi', [PublicPageController::class, 'consultation'])->name('consultation');
Route::get('/privasi', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/ketentuan', [PublicPageController::class, 'terms'])->name('terms');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
