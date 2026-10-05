<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Semua key dan model gagal atau terbatas. Pemanggil jatuh ke respons statis (FR-017, FR-028).
 * `reason` hanya kode singkat (mis. `rate_limited`, `timeout`, `http_500`), tanpa isi pesan atau key.
 */
final class AiUnavailableException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct("AI tidak tersedia: {$reason}.");
    }
}
