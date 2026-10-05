<?php

declare(strict_types=1);

namespace App\Actions\Assessment;

use App\Models\AssessmentAttempt;

/**
 * API-009: simpan jawaban PFA (terenkripsi oleh cast `user_context`).
 * Array kosong berarti pengguna melewati langkah ini; `null` berarti belum memutuskan.
 *
 * TASK-021 menambahkan pemeriksaan krisis (`CrisisDetector`) di sini sebelum mengembalikan hasil
 * (Architecture 6.6). Isi jawaban tidak boleh masuk log (RULE-040).
 */
final class SaveAssessmentContext
{
    /**
     * @param  array<string, string>  $answers  `{sub_scale: jawaban}`, sudah dibersihkan
     */
    public function handle(AssessmentAttempt $attempt, array $answers): void
    {
        $attempt->user_context = $answers;
        $attempt->save();
    }
}
