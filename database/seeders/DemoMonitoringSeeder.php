<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ConversationStatus;
use App\Enums\Intent;
use App\Enums\MessageRole;
use App\Models\Assessment;
use App\Models\Conversation;
use App\Models\User;
use App\Support\Wib;
use Illuminate\Database\Seeder;

/**
 * Data DUMMY (RULE-008) untuk menguji Monitoring admin (TASK-026). Hanya lokal.
 * Isi sengaja berupa teks contoh bertanda [DEMO], bukan percakapan sungguhan.
 *
 * Aman dijalankan ulang: dilewati bila akun demo-a sudah punya Percakapan bertanda krisis.
 */
class DemoMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoMonitoringSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        $demoA = User::query()->where('email', 'demo-a@example.test')->first();
        $assessment = Assessment::query()->where('slug', 'demo-stres-harian')->first();

        if ($demoA === null || $assessment === null) {
            $this->command?->warn('DemoMonitoringSeeder dilewati: jalankan DemoAccountSeeder dan DemoAssessmentSeeder lebih dulu.');

            return;
        }

        if ($demoA->conversations()->where('has_crisis', true)->exists()) {
            $this->command?->info('DemoMonitoringSeeder dilewati: data sudah ada.');

            return;
        }

        // Percakapan bertanda krisis. Teks hanya penanda, bukan konten krisis sungguhan.
        $this->conversation($demoA, 3, true, [
            ['[DEMO MONITORING] Pesan contoh yang ditandai krisis untuk menguji tinjauan.', null, null, true],
            ['[DEMO MONITORING] Balasan Crisis Response contoh (bukan dari AI).', Intent::CrisisSuicide, 0.9500, true],
        ]);

        // Percakapan dengan balasan keyakinan rendah.
        $this->conversation($demoA, 2, false, [
            ['[DEMO MONITORING] Aku bingung harus mulai dari mana.', null, null, false],
            ['[DEMO MONITORING] Balasan contoh dengan keyakinan rendah (bukan dari AI).', Intent::VentingStress, 0.3200, false],
        ]);

        // Percakapan biasa dengan keyakinan tinggi.
        $this->conversation($demoA, 1, false, [
            ['[DEMO MONITORING] Halo, aku mau cerita sedikit soal kuliah.', null, null, false],
            ['[DEMO MONITORING] Balasan contoh dengan keyakinan tinggi (bukan dari AI).', Intent::AcademicPressure, 0.9100, false],
        ]);

        // Hasil Asesmen: satu dengan Rekomendasi AI, satu tanpa. Jawaban dibuat kosong (tidak dipakai admin).
        $this->attempt($demoA, $assessment, 2, '[DEMO MONITORING] Rekomendasi contoh (bukan dari AI): coba istirahat sejenak dan ceritakan ke orang yang kamu percaya.');
        $this->attempt($demoA, $assessment, 4, null);
    }

    /**
     * @param  list<array{0: string, 1: Intent|null, 2: float|null, 3: bool}>  $pair  [pesan pengguna, balasan]
     */
    private function conversation(User $user, int $daysAgo, bool $crisis, array $pair): void
    {
        $startedAt = Wib::now()->subDays($daysAgo)->setTime(21, 0);

        $conversation = $user->conversations()->create([
            'status' => ConversationStatus::Active,
            'initial_intent' => $pair[1][1]?->value,
            'total_turns' => 1,
            'has_crisis' => $crisis,
            'last_message_at' => $startedAt->addMinutes(2)->utc(),
        ]);
        $conversation->forceFill(['created_at' => $startedAt->utc()])->save();

        $conversation->messages()->create([
            'turn_number' => 1,
            'role' => MessageRole::User,
            'content' => $pair[0][0],
            'is_crisis' => $pair[0][3],
        ]);
        $conversation->messages()->create([
            'turn_number' => 2,
            'role' => MessageRole::Assistant,
            'content' => $pair[1][0],
            'detected_intent' => $pair[1][1]?->value,
            'detection_confidence' => $pair[1][2],
            'is_crisis' => $pair[1][3],
        ]);
    }

    private function attempt(User $user, Assessment $assessment, int $daysAgo, ?string $recommendation): void
    {
        $user->assessmentAttempts()->create([
            'assessment_id' => $assessment->id,
            'answers' => [],
            'results' => [
                'Stres' => ['score' => 18, 'interpretation' => 'Sedang', 'severity_level' => 'moderate', 'trigger_pfa' => false],
                'Kecemasan' => ['score' => 8, 'interpretation' => 'Ringan', 'severity_level' => 'mild', 'trigger_pfa' => false],
            ],
            'ai_recommendation' => $recommendation,
            'completed_at' => Wib::now()->subDays($daysAgo)->setTime(19, 30)->utc(),
        ]);
    }
}
