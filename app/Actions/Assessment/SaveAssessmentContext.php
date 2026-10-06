<?php

declare(strict_types=1);

namespace App\Actions\Assessment;

use App\Enums\CrisisLayer;
use App\Models\AssessmentAttempt;
use App\Models\CrisisEvent;
use App\Services\Chat\CrisisDetector;
use Illuminate\Support\Facades\DB;

/**
 * API-009: simpan jawaban PFA (terenkripsi oleh cast `user_context`) dan periksa krisis (RULE-047A,
 * Architecture 6.6). Array kosong berarti pengguna melewati langkah ini; `null` berarti belum memutuskan.
 *
 * Bila ada jawaban yang terindikasi krisis: jawaban tetap tersimpan, `crisis_events` dicatat dengan
 * layer `pfa_rule` (hanya hitungan, tanpa isi dan tanpa pengguna), dan fungsi mengembalikan true.
 * Banner di halaman hasil dihitung ulang dari `user_context` setiap dibuka, dan Rekomendasi AI
 * tidak akan dibuat (jawaban tidak pernah dikirim ke AI). Isi jawaban tidak boleh masuk log (RULE-040).
 */
final class SaveAssessmentContext
{
    public function __construct(private readonly CrisisDetector $crisis) {}

    /**
     * @param  array<string, string>  $answers  `{sub_scale: jawaban}`, sudah dibersihkan
     * @return bool true bila ada jawaban yang terindikasi krisis
     */
    public function handle(AssessmentAttempt $attempt, array $answers): bool
    {
        $isCrisis = false;

        foreach ($answers as $answer) {
            if ($this->crisis->detect($answer) !== null) {
                $isCrisis = true;

                break;
            }
        }

        DB::transaction(function () use ($attempt, $answers, $isCrisis): void {
            $attempt->user_context = $answers;
            $attempt->save();

            if ($isCrisis) {
                CrisisEvent::query()->create(['layer' => CrisisLayer::PfaRule]);
            }
        });

        return $isCrisis;
    }
}
