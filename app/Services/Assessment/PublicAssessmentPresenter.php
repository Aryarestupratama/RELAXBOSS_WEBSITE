<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\Assessment;
use Illuminate\Support\Str;

/**
 * Menyusun data Asesmen untuk halaman Publik (SCR-002). Hanya metadata: butir pertanyaan, skor,
 * aturan skor, dan pengali tidak pernah ditampilkan di halaman Publik.
 */
final class PublicAssessmentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function listItem(Assessment $assessment): array
    {
        return [
            'slug' => $assessment->slug,
            'name' => $this->name($assessment),
            'summary' => Str::limit((string) $assessment->description, 160),
            'estimated_minutes' => (int) $assessment->estimated_minutes,
            'question_count' => $assessment->questions->count(),
            'sub_scales' => $this->subScales($assessment),
            'is_demo' => $assessment->isDemo(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Assessment $assessment): array
    {
        return [
            ...$this->listItem($assessment),
            'description' => (string) $assessment->description,
            'instructions' => $assessment->instructions,
            'options' => collect($assessment->options)
                ->map(static fn (array $option): string => (string) $option['label'])
                ->values()
                ->all(),
            // Klaim sumber dan validasi hanya bila data terisi dan bukan Instrumen contoh (RULE-049).
            'source' => $assessment->isDemo() ? null : $assessment->source_reference,
            'validated_by' => $assessment->isValidated() && ! $assessment->isDemo() && filled($assessment->validator_name)
                ? trim($assessment->validator_name.(filled($assessment->validator_credential) ? ', '.$assessment->validator_credential : ''))
                : null,
        ];
    }

    private function name(Assessment $assessment): string
    {
        return (string) ($assessment->display_name ?? $assessment->name);
    }

    /**
     * @return list<string>
     */
    private function subScales(Assessment $assessment): array
    {
        return $assessment->questions
            ->pluck('sub_scale')
            ->unique()
            ->values()
            ->map(static fn (mixed $name): string => (string) $name)
            ->all();
    }
}
