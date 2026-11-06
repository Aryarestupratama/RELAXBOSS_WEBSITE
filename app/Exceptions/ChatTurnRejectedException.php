<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Satu giliran chat ditolak sebelum diproses (bukan galat teknis). Membawa kode galat, pesan untuk
 * pengguna, dan status HTTP, supaya web dan API memakai teks yang sama (RULE-022).
 */
final class ChatTurnRejectedException extends RuntimeException
{
    private function __construct(
        public readonly string $errorCode,
        string $userMessage,
        public readonly int $status,
    ) {
        parent::__construct($userMessage);
    }

    public static function busy(): self
    {
        return new self('busy', 'Pesan sebelumnya masih diproses. Tunggu sebentar, ya.', 429);
    }

    public static function dailyLimit(): self
    {
        return new self(
            'daily_limit',
            'Kamu sudah mencapai batas percakapan hari ini. Besok kamu bisa lanjut bercerita. Kalau kamu butuh bantuan sekarang, lihat halaman Konsultasi Profesional.',
            429,
        );
    }

    public static function nothingToRetry(): self
    {
        return new self('nothing_to_retry', 'Tidak ada pesan yang perlu dikirim ulang. Muat ulang halaman, ya.', 409);
    }

    public static function aiUnavailable(): self
    {
        return new self(
            'ai_unavailable',
            'RelaxMate sedang tidak bisa membalas. Pesanmu tetap tersimpan. Coba lagi sebentar lagi.',
            503,
        );
    }
}
