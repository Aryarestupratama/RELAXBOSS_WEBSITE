<?php

declare(strict_types=1);

use App\Http\Controllers\App\AiConsentController;
use App\Http\Controllers\App\AssessmentController;
use App\Http\Controllers\App\ChatMessageController;
use App\Http\Controllers\App\AttemptController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\MoodController;
use App\Http\Controllers\App\RelaxMateController;
use App\Http\Controllers\App\TrainingConsentController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Dashboard (SCR-011). Path tetap /dashboard (redirect login dan verifikasi memakainya).
Route::get('/dashboard', DashboardController::class)
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
    // Consent (FR-013). API-012 mencatat consent AI; API-013 mencatat pilihan pelatihan (juga dipakai halaman Akun, TASK-022).
    Route::post('/relaxmate/consent', AiConsentController::class)->name('relaxmate.consent');
    // RelaxMate (SCR-017). Halaman GET terbuka tanpa consent agar ConsentDialog tampil; aksi AI butuh `ai.consent`.
    // Percakapan selalu dicari lewat relasi pemilik (RULE-033); {conversation} adalah UUID.
    Route::get('/relaxmate', [RelaxMateController::class, 'index'])->name('relaxmate.index');
    Route::post('/relaxmate/percakapan', [RelaxMateController::class, 'store'])
        ->middleware('ai.consent')
        ->name('relaxmate.store');
    Route::get('/relaxmate/{conversation}', [RelaxMateController::class, 'show'])
        ->whereUuid('conversation')
        ->name('relaxmate.show');
    Route::post('/relaxmate/{conversation}/pesan', [ChatMessageController::class, 'store'])
        ->whereUuid('conversation')
        ->middleware(['ai.consent', 'throttle:chat'])
        ->name('relaxmate.messages.store');
    Route::post('/akun/persetujuan-pelatihan', TrainingConsentController::class)->name('account.training-consent');
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
