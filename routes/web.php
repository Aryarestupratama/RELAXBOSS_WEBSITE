<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// SEMENTARA (TASK-002): halaman uji tampilan. Hapus di TASK-007.
Route::get('/', fn () => Inertia::render('Welcome'));

// SEMENTARA (TASK-004): pengganti Dashboard agar FR-003 bisa diuji. Diganti di TASK-014.
Route::get('/dashboard', fn () => Inertia::render('Welcome'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

if (app()->environment('local')) {
    Route::get('/uji/error/{code}', fn (int $code) => abort($code))
        ->whereIn('code', [403, 419, 429, 500, 503]);
}

// Tamu
Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');
});

// Verifikasi email (login, belum terverifikasi). Nama route dipakai framework (jangan diubah).
Route::middleware('auth')->group(function (): void {
    Route::get('/verifikasi-email', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/verifikasi-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/verifikasi-email/kirim-ulang', [EmailVerificationController::class, 'send'])
        ->name('verification.send');
});

require __DIR__.'/public.php';
