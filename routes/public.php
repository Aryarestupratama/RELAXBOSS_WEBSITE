<?php

declare(strict_types=1);

use App\Http\Controllers\PublicArticleController;
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
// Artikel edukasi (SCR-024, SCR-025). Nama `articles.*`. Draf dan terjadwal menghasilkan 404.
Route::get('/artikel', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{slug}', [PublicArticleController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('articles.show');
Route::get('/tentang', [PublicPageController::class, 'about'])->name('about');
Route::get('/konsultasi', [PublicPageController::class, 'consultation'])->name('consultation');
Route::get('/privasi', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/ketentuan', [PublicPageController::class, 'terms'])->name('terms');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
