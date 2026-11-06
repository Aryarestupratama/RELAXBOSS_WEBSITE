<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AiConsentController;
use App\Http\Controllers\Api\V1\Auth as ApiAuth;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\ConsentController;
use App\Http\Controllers\Api\V1\CrisisContactsController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\TrainingConsentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API mobile `/api/v1` (Architecture-M bagian 5)
|--------------------------------------------------------------------------
| Stateless, token Sanctum (ADR-M02). Controller ada di app/Http/Controllers/Api/V1,
| tipis, dengan FormRequest dan API Resource. Prefix `/api` dari bootstrap/app.php.
| Endpoint ditambahkan pada task fiturnya: ringkasan (TASK-M014, selesai), chat (TASK-M015),
| hapus akun (TASK-M016).
*/
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Auth (TASK-M013). Batas per akun+IP untuk login ada di Action `LoginUser`.
    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('register', ApiAuth\RegisterController::class)
            ->middleware('throttle:api-register')
            ->name('register');
        Route::post('login', ApiAuth\LoginController::class)
            ->middleware('throttle:api-guest')
            ->name('login');
        Route::post('forgot-password', ApiAuth\ForgotPasswordController::class)
            ->middleware('throttle:api-guest')
            ->name('forgot');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', ApiAuth\LogoutController::class)->name('logout');
            Route::post('resend-verification', ApiAuth\ResendVerificationController::class)->name('resend');
        });
    });

    // Konfigurasi publik (API-M007, TASK-M014): dibaca saat aplikasi dibuka, tanpa token.
    Route::get('config', ConfigController::class)
        ->middleware('throttle:api-public')
        ->name('config');

    // Ringkasan akun, consent, dan kontak bantuan (API-M006, M008 s.d. M011, TASK-M014).
    Route::middleware(['auth:sanctum', 'throttle:api-user'])->group(function (): void {
        Route::get('me', MeController::class)->name('me');
        Route::get('consent', ConsentController::class)->name('consent');
        Route::get('crisis-contacts', CrisisContactsController::class)->name('crisis-contacts');

        // Aksi tulis butuh email terverifikasi (403 `unverified`).
        Route::middleware('api.verified')->group(function (): void {
            Route::post('relaxmate/consent', AiConsentController::class)->name('relaxmate.consent');
            Route::post('account/training-consent', TrainingConsentController::class)->name('account.training-consent');
        });
    });
});
