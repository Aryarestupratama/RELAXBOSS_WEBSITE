<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Bentuk galat tunggal untuk API mobile (Architecture-M bagian 5):
 * `{ "error": { "code": "...", "message": "..." } }`, pesan Bahasa Indonesia yang aman ditampilkan.
 */
final class ApiError
{
    /**
     * @param  array<string, string|int>  $headers
     * @param  array<string, mixed>  $extra  Kunci tambahan tingkat atas, mis. `errors` untuk validasi.
     */
    public static function response(string $code, string $message, int $status, array $headers = [], array $extra = []): JsonResponse
    {
        return response()->json(
            ['error' => ['code' => $code, 'message' => $message], ...$extra],
            $status,
            $headers,
        );
    }

    /**
     * 429 `rate_limited` dengan sisa detik dari header `Retry-After`.
     *
     * @param  array<string, mixed>  $headers
     */
    public static function rateLimited(array $headers, string $messagePrefix): JsonResponse
    {
        $seconds = max(1, (int) ($headers['Retry-After'] ?? 60));

        return self::response(
            'rate_limited',
            "{$messagePrefix} Coba lagi dalam {$seconds} detik.",
            429,
            ['Retry-After' => $seconds],
        );
    }
}
