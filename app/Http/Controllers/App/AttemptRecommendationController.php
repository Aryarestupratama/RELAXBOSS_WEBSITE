<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Assessment\GenerateAssessmentRecommendation;
use App\Exceptions\AiUnavailableException;
use App\Exceptions\RecommendationSkippedException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API-010: buat Rekomendasi AI untuk hasil Asesmen (JSON). Consent AI dipasang di route (`ai.consent`).
 * Kepemilikan lewat relasi pemilik (RULE-033): hasil milik orang lain menghasilkan 404.
 * Isi hasil, jawaban PFA, dan rekomendasi tidak pernah masuk log (RULE-040).
 */
final class AttemptRecommendationController extends Controller
{
    public function store(Request $request, int $attempt, GenerateAssessmentRecommendation $action): JsonResponse
    {
        $user = $request->user();
        $record = $user->assessmentAttempts()->findOrFail($attempt);

        try {
            $result = $action->handle($user, $record);
        } catch (RecommendationSkippedException $skipped) {
            return match ($skipped->reason) {
                'crisis' => $this->error('ai_skipped', 'Rekomendasi umum ditampilkan untuk hasil ini.', 409),
                'context_pending' => $this->error('context_pending', 'Selesaikan atau lewati pertanyaan konteks lebih dulu.', 409),
                'daily_limit' => $this->error('daily_limit', 'Batas rekomendasi AI hari ini sudah tercapai. Besok kamu bisa mencobanya lagi. Sementara itu, ini rekomendasi umum untuk hasilmu.', 429),
                default => $this->error('busy', 'Rekomendasi masih disusun. Tunggu sebentar, ya.', 429),
            };
        } catch (AiUnavailableException) {
            // Alasan teknis tidak ditampilkan; klien jatuh ke rekomendasi statis (FR-010).
            return $this->error('ai_unavailable', 'Rekomendasi AI sedang tidak tersedia. Sementara itu, ini rekomendasi umum untuk hasilmu.', 503);
        }

        return response()->json(['data' => [
            'recommendation' => $result->recommendation,
            'summary' => $result->summary,
        ]]);
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
