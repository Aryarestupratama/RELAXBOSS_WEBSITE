<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Assessment;
use Illuminate\Support\Facades\DB;

/**
 * API-017 dan API-018 (FR-023): simpan Instrumen beserta pilihan, butir, dan aturan skor dalam satu transaksi.
 *
 * Butir yang sudah ada dipertahankan menurut `id` (jawaban lama memakai `question_id`), butir baru dibuat,
 * butir yang hilang dari form dihapus. Aturan skor diganti seluruhnya: hasil lama adalah salinan (`results`),
 * jadi tidak terpengaruh (Schema ENT-005). Instrumen tidak pernah dihapus (FK `assessment_attempts` restrict).
 *
 * Posisi butir dipindah dua tahap karena ada indeks unik `(assessment_id, position)`.
 */
final class SaveAssessmentDefinition
{
    /** Pergeseran sementara posisi butir agar urutan baru tidak bentrok dengan indeks unik. */
    private const TEMP_POSITION_OFFSET = 10000;

    /**
     * @param  array<string, mixed>  $attributes  kolom Instrumen (tanpa pilihan, butir, aturan)
     * @param  list<array{value: int, label: string}>  $options
     * @param  list<array{id: int|null, text: string, sub_scale: string, is_reversed: bool}>  $questions
     * @param  list<array{sub_scale: string, min_score: int, max_score: int, interpretation: string, severity_level: string, trigger_pfa: bool, pfa_question: string|null, static_recommendation: string}>  $rules
     */
    public function handle(?Assessment $assessment, array $attributes, array $options, array $questions, array $rules): Assessment
    {
        return DB::transaction(function () use ($assessment, $attributes, $options, $questions, $rules): Assessment {
            $attributes['options'] = $options;

            if ($assessment === null) {
                $assessment = Assessment::query()->create($attributes);
            } else {
                // Slug tidak berubah setelah dibuat (tautan Publik).
                unset($attributes['slug']);
                $assessment->update($attributes);
            }

            $this->syncQuestions($assessment, $questions);
            $this->replaceRules($assessment, $rules);

            return $assessment->refresh();
        });
    }

    /**
     * @param  list<array{id: int|null, text: string, sub_scale: string, is_reversed: bool}>  $questions
     */
    private function syncQuestions(Assessment $assessment, array $questions): void
    {
        $keepIds = array_values(array_filter(
            array_map(static fn (array $question): ?int => $question['id'], $questions),
            static fn (?int $id): bool => $id !== null,
        ));

        // Hapus butir yang dibuang admin lebih dulu, lalu geser sisanya ke posisi sementara.
        $assessment->questions()->getQuery()->reorder()->whereNotIn('id', $keepIds)->delete();
        $assessment->questions()->getQuery()->reorder()->increment('position', self::TEMP_POSITION_OFFSET);

        foreach ($questions as $index => $question) {
            $values = [
                'position' => $index + 1,
                'text' => $question['text'],
                'sub_scale' => $question['sub_scale'],
                'is_reversed' => $question['is_reversed'],
            ];

            if ($question['id'] !== null) {
                $assessment->questions()->getQuery()->reorder()->whereKey($question['id'])->update($values);
            } else {
                $assessment->questions()->create($values);
            }
        }
    }

    /**
     * @param  list<array{sub_scale: string, min_score: int, max_score: int, interpretation: string, severity_level: string, trigger_pfa: bool, pfa_question: string|null, static_recommendation: string}>  $rules
     */
    private function replaceRules(Assessment $assessment, array $rules): void
    {
        $assessment->scoringRules()->getQuery()->reorder()->delete();

        foreach ($rules as $rule) {
            $assessment->scoringRules()->create($rule);
        }
    }
}
