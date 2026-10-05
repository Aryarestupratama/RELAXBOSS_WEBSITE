<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Tidak ada aturan skor yang menutup skor sebuah subskala (celah rentang, Schema ENT-004).
 * Pesan hanya memuat ID dan nama subskala, tanpa skor atau jawaban (RULE-040).
 */
final class ScoringRuleNotFoundException extends RuntimeException
{
    public static function forSubScale(int $assessmentId, string $subScale): self
    {
        return new self("Aturan skor tidak ditemukan: assessment_id={$assessmentId}, sub_scale={$subScale}.");
    }
}
