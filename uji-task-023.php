<?php

// Data uji TASK-023. Dijalankan lewat tinker; TIDAK dimasukkan ke repo (hanya untuk lokal).
// Setelah selesai uji: php artisan migrate:fresh --seed

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$assessment = Assessment::query()->firstOrFail();

for ($i = 1; $i <= 7; $i++) {
    $created = now()->subDays(20);

    $user = User::factory()->create([
        'email' => "kpi{$i}@example.test",
        'created_at' => $created,
        'updated_at' => $created,
    ]);

    // user 5: Asesmen terlambat (hari ke-10); user 7: tanpa Asesmen; sisanya hari ke-2
    $offset = match ($i) {
        5 => 10,
        7 => null,
        default => 2,
    };

    if ($offset !== null) {
        $user->assessmentAttempts()->create([
            'assessment_id' => $assessment->id,
            'answers' => [],
            'results' => [],
            'completed_at' => $created->copy()->addDays($offset),
        ]);
    }

    // user 1 dan 2: mood di 3 hari berbeda dalam 14 hari sejak Asesmen
    if ($i <= 2) {
        foreach ([3, 4, 5] as $day) {
            $when = $created->copy()->addDays($day);
            $user->moodEntries()->create([
                'mood' => 3,
                'entry_date' => $when->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'logged_at' => $when,
            ]);
        }
    }
}

foreach (['rule' => 6, 'pfa_rule' => 2] as $layer => $total) {
    for ($n = 0; $n < $total; $n++) {
        DB::table('crisis_events')->insert(['layer' => $layer, 'created_at' => now()]);
    }
}

$today = now()->utc()->toDateString();

DB::table('ai_usage_daily')->insert([
    ['usage_date' => $today, 'model' => 'openai/gpt-oss-120b', 'feature' => 'chat', 'requests' => 12, 'rate_limited' => 1, 'errors' => 0, 'prompt_tokens' => 3000, 'completion_tokens' => 900],
    ['usage_date' => $today, 'model' => 'openai/gpt-oss-120b', 'feature' => 'assessment', 'requests' => 3, 'rate_limited' => 0, 'errors' => 1, 'prompt_tokens' => 800, 'completion_tokens' => 400],
]);

echo "Data uji TASK-023 dibuat.\n";
