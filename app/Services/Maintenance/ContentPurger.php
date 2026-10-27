<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\AdminAccessLog;
use App\Models\AiUsageDaily;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScoringRule;
use App\Models\Conversation;
use App\Models\CrisisEvent;
use App\Models\Message;
use App\Models\MoodEntry;
use Illuminate\Support\Facades\DB;

/**
 * Membersihkan konten dan data aktivitas sebelum hosting (TASK-031), tanpa menyentuh akun.
 *
 * Dihapus: Instrumen beserta butir dan aturan skornya, hasil Asesmen, artikel, percakapan dan pesan
 * RelaxMate, kejadian krisis, catatan mood, log akses admin, dan rekap pemakaian AI.
 * Tidak disentuh: tabel `users` (dan foto profil), sesi, token reset sandi, cache.
 *
 * Urutan penghapusan mengikuti foreign key: hasil Asesmen dulu karena memakai `restrictOnDelete`
 * ke Instrumen. Memakai DELETE (bukan TRUNCATE) agar aman terhadap foreign key di MySQL.
 */
final class ContentPurger
{
    /**
     * Jumlah baris per tabel yang akan atau sudah dihapus. Kunci: nama tabel.
     *
     * @return array<string, int>
     */
    public function counts(): array
    {
        $counts = [];

        foreach ($this->tables() as $table => $model) {
            $counts[$table] = $model::query()->count();
        }

        return $counts;
    }

    /**
     * @return array<string, int> jumlah baris yang dihapus per tabel
     */
    public function purge(): array
    {
        return DB::transaction(function (): array {
            $deleted = [];

            foreach ($this->tables() as $table => $model) {
                $deleted[$table] = $model::query()->delete();
            }

            return $deleted;
        });
    }

    /**
     * Urutan penting: anak sebelum induk.
     *
     * @return array<string, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private function tables(): array
    {
        return [
            'assessment_attempts' => AssessmentAttempt::class,
            'assessment_scoring_rules' => AssessmentScoringRule::class,
            'assessment_questions' => AssessmentQuestion::class,
            'assessments' => Assessment::class,
            'articles' => Article::class,
            'messages' => Message::class,
            'conversations' => Conversation::class,
            'crisis_events' => CrisisEvent::class,
            'mood_entries' => MoodEntry::class,
            'admin_access_logs' => AdminAccessLog::class,
            'ai_usage_daily' => AiUsageDaily::class,
        ];
    }
}
