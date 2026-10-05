<?php

declare(strict_types=1);

namespace App\Actions\Assessment;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\Assessment\ScoringService;

/**
 * API-008: hitung skor di server lalu simpan hasil yang selesai.
 * `answers` disimpan mentah (terenkripsi oleh cast); `results` adalah salinan per subskala.
 */
final class SaveAssessmentAttempt
{
    public function __construct(private readonly ScoringService $scoring) {}

    /**
     * @param  array<int, int>  $answers  `{question_id: nilai}`, sudah divalidasi
     */
    public function handle(User $user, Assessment $assessment, array $answers): AssessmentAttempt
    {
        $assessment->loadMissing(['questions', 'scoringRules']);

        $results = $this->scoring->score($assessment, $answers);

        return $user->assessmentAttempts()->create([
            'assessment_id' => $assessment->id,
            'answers' => $answers,
            'results' => $results,
            'completed_at' => now(),
        ]);
    }
}
