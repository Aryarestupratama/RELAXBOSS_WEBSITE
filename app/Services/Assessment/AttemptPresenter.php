<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentScoringRule;
use App\Support\Wib;
use Illuminate\Support\Collection;

/**
 * Menyusun data hasil untuk ditampilkan (SCR-014, SCR-015).
 *
 * `results` adalah salinan saat pengerjaan, jadi riwayat tidak berubah bila admin mengubah rentang.
 * Teks yang tidak ada di salinan (rekomendasi statis, pertanyaan PFA, batas atas meter) diambil dari
 * aturan skor saat ini, dicocokkan lewat subskala dan interpretasi (bukan lewat skor). Bila aturannya
 * sudah dihapus, bagian itu dikosongkan, bukan galat.
 */
final class AttemptPresenter
{
    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * @return array<string, mixed>
     */
    public function detail(AssessmentAttempt $attempt): array
    {
        $attempt->loadMissing(['assessment', 'assessment.scoringRules']);

        $rules = $attempt->assessment->scoringRules->groupBy('sub_scale');

        /** @var array<string, array{score: int, interpretation: string, severity_level: string, trigger_pfa: bool}> $results */
        $results = $attempt->results;

        $subScales = [];
        foreach ($results as $name => $result) {
            /** @var Collection<int, AssessmentScoringRule> $subRules */
            $subRules = $rules->get($name, collect());
            $rule = $subRules->first(
                static fn (AssessmentScoringRule $candidate): bool => $candidate->interpretation === $result['interpretation'],
            );

            $subScales[] = [
                'name' => (string) $name,
                'score' => $result['score'],
                'max_score' => max($result['score'], (int) $subRules->max('max_score')),
                'interpretation' => $result['interpretation'],
                'severity_level' => $result['severity_level'],
                'trigger_pfa' => $result['trigger_pfa'],
                'pfa_question' => $result['trigger_pfa'] ? $rule?->pfa_question : null,
                'recommendation' => $rule?->static_recommendation,
            ];
        }

        /** @var array<string, string>|null $context */
        $context = $attempt->user_context;

        return [
            'id' => $attempt->id,
            'assessment_name' => $attempt->assessment->display_name ?? $attempt->assessment->name,
            'completed_at' => $this->formatDate($attempt),
            'sub_scales' => $subScales,
            // null = belum memutuskan; array (boleh kosong) = sudah menjawab atau melewati.
            'context' => $context,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(AssessmentAttempt $attempt): array
    {
        /** @var array<string, array{score: int, interpretation: string, severity_level: string}> $results */
        $results = $attempt->results;

        return [
            'id' => $attempt->id,
            'assessment_name' => $attempt->assessment->display_name ?? $attempt->assessment->name,
            'completed_at' => $this->formatDate($attempt),
            'sub_scales' => collect($results)
                ->map(static fn (array $result, string $name): array => [
                    'name' => $name,
                    'interpretation' => $result['interpretation'],
                    'severity_level' => $result['severity_level'],
                ])
                ->values()
                ->all(),
        ];
    }

    private function formatDate(AssessmentAttempt $attempt): string
    {
        $moment = Wib::toWib($attempt->completed_at);

        return sprintf(
            '%d %s %d, %s WIB',
            $moment->day,
            self::MONTHS[$moment->month],
            $moment->year,
            $moment->format('H.i'),
        );
    }
}
