<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Login gagal. Pesan aman ditampilkan (Bahasa Indonesia). Web mengubahnya menjadi galat form `login`,
 * API mengubahnya menjadi amplop galat dengan `errorCode` dan `status` ini (API-M002).
 */
final class LoginFailedException extends RuntimeException
{
    private function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $status,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }

    public static function throttled(int $seconds): self
    {
        return new self(
            "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
            'rate_limited',
            429,
            $seconds,
        );
    }

    public static function invalidCredentials(): self
    {
        return new self('Email atau kata sandi tidak cocok.', 'invalid_credentials', 401);
    }

    public static function inactive(): self
    {
        return new self(
            'Akunmu sedang dinonaktifkan. Hubungi kami bila menurutmu ini keliru.',
            'account_inactive',
            403,
        );
    }
}
