<?php

declare(strict_types=1);

namespace App\Actions\Mood;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use App\Models\MoodEntry;
use App\Models\User;
use App\Support\Wib;
use Illuminate\Support\Facades\DB;

/**
 * API-011: buat Mood Entry. Mengembalikan null bila batas harian (WIB) sudah tercapai.
 *
 * `logged_at` disimpan UTC, `entry_date` dihitung WIB hanya lewat `Wib` (RULE-035).
 * Catatan tidak boleh masuk log (RULE-040); `note` terenkripsi oleh cast model (RULE-032).
 */
final class CreateMoodEntry
{
    public function handle(User $user, MoodLevel $mood, ?ArousalInput $arousal, ?string $note): ?MoodEntry
    {
        return DB::transaction(function () use ($user, $mood, $arousal, $note): ?MoodEntry {
            // Kunci baris pengguna agar dua permintaan bersamaan tidak melewati batas harian.
            User::query()->whereKey($user->getKey())->lockForUpdate()->first();

            $now = Wib::now();
            $today = $now->format('Y-m-d');
            $tomorrow = $now->addDay()->format('Y-m-d');

            // Rentang, bukan `=`: di SQLite `entry_date` tersimpan sebagai "Y-m-d 00:00:00" (cast `date`).
            $countToday = $user->moodEntries()
                ->where('entry_date', '>=', $today)
                ->where('entry_date', '<', $tomorrow)
                ->count();
            if ($countToday >= (int) config('relaxboss.limits.mood_per_day')) {
                return null;
            }

            $note = $note === null ? null : trim($note);

            return $user->moodEntries()->create([
                'mood' => $mood,
                'arousal_input' => $arousal,
                'note' => $note === '' ? null : $note,
                'entry_date' => $today,
                'logged_at' => $now->utc(),
            ]);
        });
    }
}
