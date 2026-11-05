<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth as ApiAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API mobile `/api/v1` (Architecture-M bagian 5)
|--------------------------------------------------------------------------
| Stateless, token Sanctum (ADR-M02). Controller ada di app/Http/Controllers/Api/V1,
| tipis, dengan FormRequest dan API Resource. Prefix `/api` dari bootstrap/app.php.
| Endpoint ditambahkan pada task fiturnya: ringkasan (TASK-M014), chat (TASK-M015),
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
});
