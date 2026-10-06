<?php

declare(strict_types=1);

namespace App\Actions\Assessment;

use App\DTOs\AssessmentRecommendation;
use App\Exceptions\AiUnavailableException;
use App\Exceptions\RecommendationSkippedException;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\Assessment\AttemptPresenter;
use App\Services\Assessment\RecommendationQuota;
use App\Services\Assessment\RecommendationService;
use Illuminate\Support\Facades\Cache;

/**
 * API-010: buat Rekomendasi AI untuk satu hasil dan simpan terenkripsi (`ai_recommendation`,
 * `ai_summary`). Hanya sekali per hasil: bila sudah ada, dikembalikan tanpa memanggil AI.
 *
 * Urutan penjagaan: sudah ada, langkah PFA belum diputuskan, krisis, kunci per hasil, batas harian.
 * Pemanggil (controller) memastikan kepemilikan dan consent AI.
 */
final class GenerateAssessmentRecommendation
{
    public function __construct(
        private readonly RecommendationService $service,
        private readonly RecommendationQuota $quota,
        private readonly AttemptPresenter $presenter,
    ) {}

    /**
     * @throws RecommendationSkippedException
     * @throws AiUnavailableException
     */
    public function handle(User $user, AssessmentAttempt $attempt): AssessmentRecommendation
    {
        if (filled($attempt->ai_recommendation)) {
            return new AssessmentRecommendation($attempt->ai_recommendation, $attempt->ai_summary);
        }

        $detail = $this->presenter->detail($attempt);

        if ($detail['crisis'] === true) {
            throw new RecommendationSkippedException('crisis');
        }

        if ($detail['context'] === null && $this->hasPfaStep($detail)) {
            throw new RecommendationSkippedException('context_pending');
        }

        // Dua permintaan untuk hasil yang sama tidak diproses bersamaan (klik ganda, muat ulang).
        $lock = Cache::lock('assessment-ai:attempt:'.$attempt->getKey(), 60);

        if (! $lock->get()) {
            throw new RecommendationSkippedException('busy');
        }

        try {
            // Periksa lagi setelah memegang kunci: permintaan lain mungkin sudah selesai.
            $attempt->refresh();

            if (filled($attempt->ai_recommendation)) {
                return new AssessmentRecommendation($attempt->ai_recommendation, $attempt->ai_summary);
            }

            if ($this->quota->exhausted($user)) {
                throw new RecommendationSkippedException('daily_limit');
            }

            $result = $this->service->generate($user, $attempt);

            $attempt->forceFill([
                'ai_recommendation' => $result->recommendation,
                'ai_summary' => $result->summary,
            ])->save();

            $this->quota->consume($user);

            return $result;
        } finally {
            $lock->release();
        }
    }

    /**
     * Langkah PFA tampil bila ada subskala pemicu yang punya pertanyaan (sama dengan halaman hasil).
     *
     * @param  array<string, mixed>  $detail
     */
    private function hasPfaStep(array $detail): bool
    {
        /** @var list<array<string, mixed>> $subScales */
        $subScales = $detail['sub_scales'];

        foreach ($subScales as $subScale) {
            if (($subScale['trigger_pfa'] ?? false) === true && filled($subScale['pfa_question'] ?? null)) {
                return true;
            }
        }

        return false;
    }
}
