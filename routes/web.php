<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// SEMENTARA (TASK-002): halaman uji tampilan. Hapus di TASK-007.
Route::get('/', fn () => Inertia::render('Welcome'));

// SEMENTARA (TASK-004/005): pengganti Dashboard agar FR-003 dan logout bisa diuji. Diganti di TASK-014.
Route::get('/dashboard', fn () => Inertia::render('DashboardSementara'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

if (app()->environment('local')) {
    Route::get('/uji/error/{code}', fn (int $code) => abort($code))
        ->whereIn('code', [403, 419, 429, 500, 503]);
}

// Tamu. Nama route berikut dipakai framework (jangan diubah): login, password.*, logout.
Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])
        ->middleware('throttle:register')
        ->name('register.store');

    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->name('login.store');

    Route::get('/lupa-sandi', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/lupa-sandi', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-sandi/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-sandi', [NewPasswordController::class, 'store'])->name('password.store');
});

// Login. /keluar sengaja tanpa middleware `active` (TASK-006) agar akun nonaktif tetap bisa keluar.
Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', LogoutController::class)->name('logout');

    Route::get('/verifikasi-email', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('/verifikasi-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/verifikasi-email/kirim-ulang', [EmailVerificationController::class, 'send'])
        ->name('verification.send');
});

require __DIR__.'/public.php';
