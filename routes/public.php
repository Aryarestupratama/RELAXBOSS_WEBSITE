<?php

declare(strict_types=1);

use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Halaman Publik (Blade): FR-020, FR-022. Info Asesmen (/asesmen) menyusul di TASK-011.
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/tentang', [PublicPageController::class, 'about'])->name('about');
Route::get('/konsultasi', [PublicPageController::class, 'consultation'])->name('consultation');
Route::get('/privasi', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/ketentuan', [PublicPageController::class, 'terms'])->name('terms');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
