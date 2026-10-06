<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Rekomendasi AI sengaja tidak dibuat (bukan galat penyedia AI). Pemanggil menampilkan
 * rekomendasi statis. `reason`: `context_pending`, `crisis`, `daily_limit`, atau `busy`.
 */
final class RecommendationSkippedException extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct("Rekomendasi AI dilewati: {$reason}.");
    }
}
