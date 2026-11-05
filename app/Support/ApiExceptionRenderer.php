<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

/**
 * Menyeragamkan galat di bawah `/api/*` ke amplop `{ "error": {...} }`.
 * Rute web tidak tersentuh (mengembalikan null agar ditangani seperti biasa).
 * Galat 500 sengaja tidak dirender di sini: tetap lewat penanganan bawaan yang tidak
 * membocorkan jejak tumpukan di produksi (RULE-040).
 */
final class ApiExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof AuthenticationException => ApiError::response(
                    'unauthenticated',
                    'Sesimu sudah berakhir. Silakan masuk lagi.',
                    401,
                ),
                $e instanceof ValidationException => self::validation($e),
                $e instanceof AccessDeniedHttpException => ApiError::response(
                    'forbidden',
                    'Kamu belum bisa mengakses ini.',
                    403,
                ),
                $e instanceof NotFoundHttpException => ApiError::response(
                    'not_found',
                    'Yang kamu cari tidak ditemukan.',
                    404,
                ),
                $e instanceof TooManyRequestsHttpException => ApiError::rateLimited(
                    $e->getHeaders(),
                    'Terlalu banyak percobaan.',
                ),
                $e instanceof HttpExceptionInterface => ApiError::response(
                    'http_error',
                    'Permintaan tidak dapat diproses.',
                    $e->getStatusCode(),
                ),
                default => null,
            };
        });
    }

    private static function validation(ValidationException $e): JsonResponse
    {
        $errors = $e->errors();
        $first = collect($errors)->flatten()->first();

        return ApiError::response(
            'validation_failed',
            is_string($first) && $first !== '' ? $first : 'Periksa lagi isianmu, ya.',
            422,
            [],
            ['errors' => $errors],
        );
    }
}
