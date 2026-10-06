<?php

declare(strict_types=1);

namespace App\DTOs;

/** Rekomendasi AI untuk satu hasil Asesmen (FR-010). `summary` boleh kosong. */
final readonly class AssessmentRecommendation
{
    public function __construct(
        public string $recommendation,
        public ?string $summary,
    ) {}
}
