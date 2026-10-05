<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArousalInput;
use App\Models\User;
use App\Support\Wib;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Catatan mood DUMMY (RULE-008) untuk akun `example.test`. Hanya lokal.
 * Dipakai menguji grafik 7 dan 30 hari di TASK-013, termasuk hari dengan beberapa entri.
 *
 * Idempotent: catatan mood akun demo dihapus lalu dibuat ulang setiap dijalankan
 * (catatan yang Anda buat sendiri di akun demo ikut terhapus).
 */
class DemoMoodSeeder extends Seeder
{
    /** Pola mood harian (diulang) untuk akun A. */
    private const PATTERN = [3, 4, 3, 2, 4, 5, 3, 2, 3, 4, 4, 3, 1, 2, 3, 4, 5, 4, 3, 3, 2, 3, 4, 5, 4, 3, 2, 3, 4, 4];

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoMoodSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        $demoA = User::query()->where('email', 'demo-a@example.test')->first();
        $demoB = User::query()->where('email', 'demo-b@example.test')->first();

        if ($demoA === null || $demoB === null) {
            $this->command?->warn('DemoMoodSeeder dilewati: jalankan DemoAccountSeeder lebih dulu.');

            return;
        }

        DB::transaction(function () use ($demoA, $demoB): void {
            $this->seedUser($demoA, 30, true);
            $this->seedUser($demoB, 3, false);
        });
    }

    private function seedUser(User $user, int $days, bool $varied): void
    {
        $user->moodEntries()->delete();

        $now = Wib::now();

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $day = $now->subDays($offset);
            $base = self::PATTERN[$offset % count(self::PATTERN)];

            // Jam WIB; hari tertentu punya entri kedua atau ketiga (mood pagi dan siang bisa berbeda).
            $slots = [[8, 0, $base]];
            if ($varied && $offset % 3 === 0) {
                $slots[] = [13, 15, max(1, min(5, $base + ($offset % 2 === 0 ? -1 : 1)))];
            }
            if ($varied && $offset % 7 === 0) {
                $slots[] = [20, 30, $base];
            }

            foreach ($slots as $index => [$hour, $minute, $mood]) {
                $moment = $day->setTime($hour, $minute);

                if ($moment->greaterThan($now)) {
                    continue; // jangan membuat catatan di masa depan
                }

                $user->moodEntries()->create([
                    'mood' => $mood,
                    'arousal_input' => $varied && $index === 0 && $offset % 2 === 0
                        ? ($mood >= 4 ? ArousalInput::Energized : ArousalInput::Tired)
                        : null,
                    'note' => $varied
                        ? ($offset % 5 === 0 ? '[DEMO] Catatan contoh hari ke-'.$offset.'.' : null)
                        : '[DEMO B] Catatan akun B, tidak boleh terlihat oleh akun A.',
                    // Simpan UTC: Eloquent memformat apa adanya, jadi konversi eksplisit (RULE-035).
                    'logged_at' => $moment->utc(),
                    'entry_date' => Wib::date($moment),
                ]);
            }
        }
    }
}
