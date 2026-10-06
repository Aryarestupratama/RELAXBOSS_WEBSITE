<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\SeverityLevel;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScoringRule;
use App\Support\Aggregate;

/**
 * SCR-020: data tampilan untuk daftar dan form Instrumen. Hanya definisi Instrumen;
 * tidak ada jawaban atau hasil pengguna (RULE-042). Jumlah pengerjaan disamarkan bila < 5.
 */
final class AssessmentAdminPresenter
{
    /**
     * @return list<array{id: int, slug: string, name: string, is_active: bool, is_demo: bool, is_validated: bool, question_count: int, rule_count: int, attempts: string, sort_order: int}>
     */
    public function list(): array
    {
        return Assessment::query()
            ->withCount(['questions', 'scoringRules', 'attempts'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Assessment $assessment): array => [
                'id' => $assessment->id,
                'slug' => $assessment->slug,
                'name' => $assessment->display_name ?? $assessment->name,
                'is_active' => $assessment->is_active,
                'is_demo' => $assessment->isDemo(),
                'is_validated' => $assessment->isValidated(),
                'question_count' => (int) $assessment->questions_count,
                'rule_count' => (int) $assessment->scoring_rules_count,
                'attempts' => Aggregate::count((int) $assessment->attempts_count),
                'sort_order' => $assessment->sort_order,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function form(?Assessment $assessment): array
    {
        $severityLevels = array_map(static fn (SeverityLevel $level): string => $level->value, SeverityLevel::cases());
        $limits = (array) config('relaxboss.admin.assessment');

        if ($assessment === null) {
            return [
                'mode' => 'create',
                'id' => null,
                'version' => 'new',
                'has_attempts' => false,
                'attempts' => '0',
                'values' => [
                    'slug' => '',
                    'name' => '',
                    'display_name' => '',
                    'description' => '',
                    'instructions' => '',
                    'estimated_minutes' => 5,
                    'score_multiplier' => '1.00',
                    'source_reference' => '',
                    'creator_name' => '',
                    'creator_institution' => '',
                    'validator_name' => '',
                    'validator_credential' => '',
                    'validated_at' => '',
                    'is_active' => false,
                    'sort_order' => 0,
                    'options' => [
                        ['value' => 0, 'label' => ''],
                        ['value' => 1, 'label' => ''],
                    ],
                    'questions' => [],
                    'rules' => [],
                ],
                'severity_levels' => $severityLevels,
                'limits' => $limits,
            ];
        }

        $assessment->load(['questions', 'scoringRules']);
        $attempts = (int) $assessment->attempts()->count();

        return [
            'mode' => 'edit',
            'id' => $assessment->id,
            // Berubah bila daftar ID butir berubah, sehingga formulir dimuat ulang dan ID butir baru ikut terbaca.
            'version' => md5($assessment->questions->pluck('id')->implode(',')),
            'has_attempts' => $attempts > 0,
            'attempts' => Aggregate::count($attempts),
            'values' => [
                'slug' => $assessment->slug,
                'name' => $assessment->name,
                'display_name' => $assessment->display_name ?? '',
                'description' => $assessment->description,
                'instructions' => $assessment->instructions ?? '',
                'estimated_minutes' => $assessment->estimated_minutes,
                'score_multiplier' => (string) $assessment->score_multiplier,
                'source_reference' => $assessment->source_reference ?? '',
                'creator_name' => $assessment->creator_name ?? '',
                'creator_institution' => $assessment->creator_institution ?? '',
                'validator_name' => $assessment->validator_name ?? '',
                'validator_credential' => $assessment->validator_credential ?? '',
                'validated_at' => $assessment->validated_at?->format('Y-m-d') ?? '',
                'is_active' => $assessment->is_active,
                'sort_order' => $assessment->sort_order,
                'options' => collect($assessment->options)
                    ->map(fn (array $option): array => [
                        'value' => (int) $option['value'],
                        'label' => (string) $option['label'],
                    ])
                    ->values()
                    ->all(),
                'questions' => $assessment->questions
                    ->map(fn (AssessmentQuestion $question): array => [
                        'id' => $question->id,
                        'text' => $question->text,
                        'sub_scale' => $question->sub_scale,
                        'is_reversed' => $question->is_reversed,
                    ])
                    ->values()
                    ->all(),
                'rules' => $assessment->scoringRules
                    ->map(fn (AssessmentScoringRule $rule): array => [
                        'sub_scale' => $rule->sub_scale,
                        'min_score' => $rule->min_score,
                        'max_score' => $rule->max_score,
                        'interpretation' => $rule->interpretation,
                        'severity_level' => $rule->severity_level->value,
                        'trigger_pfa' => $rule->trigger_pfa,
                        'pfa_question' => $rule->pfa_question ?? '',
                        'static_recommendation' => $rule->static_recommendation,
                    ])
                    ->values()
                    ->all(),
            ],
            'severity_levels' => $severityLevels,
            'limits' => $limits,
        ];
    }
}
