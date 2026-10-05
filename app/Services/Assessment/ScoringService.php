<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Exceptions\ScoringRuleNotFoundException;
use App\Models\Assessment;
use App\Models\AssessmentScoringRule;

/**
 * FR-007: skor dihitung di server per subskala.
 * Urutan: nilai jawaban, pembalikan butir terbalik, jumlah per subskala, pengali Instrumen,
 * lalu pencocokan dengan aturan skor (rentang inklusif).
 *
 * Pembalikan (A-3, divalidasi per Instrumen asli di TASK-030): nilai terbalik = (nilai maks + nilai min) - nilai,
 * dengan min dan maks diambil dari `assessments.options`.
 */
final class ScoringService
{
    /**
     * Nilai yang diterima untuk sebuah butir, dari `assessments.options`.
     *
     * @return list<int>
     */
    public function allowedValues(Assessment $assessment): array
    {
        /** @var list<array{value: int|string, label: string}> $options */
        $options = $assessment->options;

        return array_values(array_map(static fn (array $option): int => (int) $option['value'], $options));
    }

    /**
     * @param  array<int, int>  $answers  `{question_id: nilai}`, sudah divalidasi lengkap dan sesuai pilihan
     * @return array<string, array{score: int, interpretation: string, severity_level: string, trigger_pfa: bool}>
     *
     * @throws ScoringRuleNotFoundException
     */
    public function score(Assessment $assessment, array $answers): array
    {
        $allowed = $this->allowedValues($assessment);
        $min = min($allowed);
        $max = max($allowed);
        $multiplier = (float) $assessment->score_multiplier;

        /** @var array<string, int> $sums */
        $sums = [];

        foreach ($assessment->questions as $question) {
            $value = (int) $answers[$question->id];

            if ($question->is_reversed) {
                $value = ($max + $min) - $value;
            }

            $sums[$question->sub_scale] = ($sums[$question->sub_scale] ?? 0) + $value;
        }

        $rules = $assessment->scoringRules->groupBy('sub_scale');
        $results = [];

        foreach ($sums as $subScale => $sum) {
            $score = (int) round($sum * $multiplier);

            /** @var AssessmentScoringRule|null $rule */
            $rule = $rules->get($subScale)?->first(
                static fn (AssessmentScoringRule $candidate): bool => $score >= $candidate->min_score
                    && $score <= $candidate->max_score,
            );

            if ($rule === null) {
                throw ScoringRuleNotFoundException::forSubScale($assessment->id, (string) $subScale);
            }

            $results[(string) $subScale] = [
                'score' => $score,
                'interpretation' => $rule->interpretation,
                'severity_level' => $rule->severity_level->value,
                'trigger_pfa' => $rule->trigger_pfa,
            ];
        }

        return $results;
    }
}
