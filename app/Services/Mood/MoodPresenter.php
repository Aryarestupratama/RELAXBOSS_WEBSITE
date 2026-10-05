<?php

declare(strict_types=1);

namespace App\Services\Mood;

use App\Models\MoodEntry;
use App\Models\User;
use App\Support\Wib;

/**
 * Menyusun data Mood Tracker untuk tampilan (SCR-016, FR-012).
 * Semua query lewat relasi pemilik (RULE-033). Catatan hanya dikirim ke pemiliknya.
 */
final class MoodPresenter
{
    /** Jumlah hari yang dimuat untuk grafik (mode 30 hari mencakup mode 7 hari). */
    public const CHART_DAYS = 30;

    private const MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Data halaman: entri hari ini, sisa batas, dan 30 hari terakhir (terlama dulu).
     *
     * @return array{today: list<array<string, mixed>>, limit: int, remaining: int, days: list<array<string, mixed>>}
     */
    public function page(User $user): array
    {
        $now = Wib::now();
        $today = $now->format('Y-m-d');
        $startDate = $now->startOfDay()->subDays(self::CHART_DAYS - 1)->format('Y-m-d');

        $entries = $user->moodEntries()
            ->where('entry_date', '>=', $startDate)
            ->orderBy('logged_at')
            ->orderBy('id')
            ->get()
            ->groupBy(static fn (MoodEntry $entry): string => $entry->entry_date->format('Y-m-d'));

        $days = [];
        for ($offset = self::CHART_DAYS - 1; $offset >= 0; $offset--) {
            $day = $now->startOfDay()->subDays($offset);
            $key = $day->format('Y-m-d');
            $dayEntries = $entries->get($key, collect());

            $days[] = [
                'date' => $key,
                'label' => $day->day.' '.self::MONTHS[$day->month],
                'is_today' => $key === $today,
                // Hari tanpa entri = celah (null), bukan nol.
                'average' => $dayEntries->isEmpty()
                    ? null
                    : round((float) $dayEntries->avg(static fn (MoodEntry $entry): int => $entry->mood->value), 2),
                'entries' => $dayEntries
                    ->map(fn (MoodEntry $entry): array => $this->entry($entry))
                    ->values()
                    ->all(),
            ];
        }

        $todayEntries = $entries->get($today, collect());
        $limit = (int) config('relaxboss.limits.mood_per_day');

        return [
            'today' => $todayEntries->map(fn (MoodEntry $entry): array => $this->entry($entry))->values()->all(),
            'limit' => $limit,
            'remaining' => max(0, $limit - $todayEntries->count()),
            'days' => $days,
        ];
    }

    /**
     * @return array{id: int, time: string, minutes: int, mood: int, mood_label: string, arousal: string|null, arousal_label: string|null, note: string|null}
     */
    private function entry(MoodEntry $entry): array
    {
        $moment = Wib::toWib($entry->logged_at);

        return [
            'id' => $entry->id,
            'time' => $moment->format('H:i'),
            'minutes' => $moment->hour * 60 + $moment->minute,
            'mood' => $entry->mood->value,
            'mood_label' => $entry->mood->label(),
            'arousal' => $entry->arousal_input?->value,
            'arousal_label' => $entry->arousal_input?->label(),
            'note' => $entry->note,
        ];
    }
}
