<?php

declare(strict_types=1);

use App\Http\Controllers\App\AssessmentController;
use App\Http\Controllers\App\AttemptController;
use App\Http\Controllers\App\MoodController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// SEMENTARA (TASK-004/005): pengganti Dashboard agar FR-003 dan logout bisa diuji. Diganti di TASK-014.
Route::get('/dashboard', fn () => Inertia::render('DashboardSementara'))
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

// Area aplikasi (login, terverifikasi, aktif). Asesmen yang nonaktif atau tidak ada menghasilkan 404.
Route::middleware(['auth', 'verified', 'active'])->prefix('app')->name('app.')->group(function (): void {
    Route::get('/asesmen', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/asesmen/{activeAssessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('/asesmen/{activeAssessment}/hasil', [AssessmentController::class, 'store'])->name('assessments.store');
    // Hasil dan riwayat (SCR-014, SCR-015). Selalu lewat relasi pemilik (RULE-033).
    Route::get('/riwayat', [AttemptController::class, 'index'])->name('attempts.index');
    Route::get('/riwayat/{attempt}', [AttemptController::class, 'show'])
        ->whereNumber('attempt')
        ->name('attempts.show');
    Route::post('/riwayat/{attempt}/konteks', [AttemptController::class, 'storeContext'])
        ->whereNumber('attempt')
        ->name('attempts.context');
    // Mood Tracker (SCR-016, API-011). Entri selalu lewat relasi pemilik (RULE-033).
    Route::get('/mood', [MoodController::class, 'index'])->name('mood.index');
    Route::post('/mood', [MoodController::class, 'store'])
        ->middleware('throttle:mood')
        ->name('mood.store');
});

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

// Login. /keluar sengaja tanpa middleware `active` agar akun nonaktif tetap bisa keluar.
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
