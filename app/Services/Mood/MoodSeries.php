<?php

declare(strict_types=1);

namespace App\Services\Mood;

use App\Models\MoodEntry;
use App\Models\User;
use App\Support\Wib;
use Carbon\CarbonImmutable;

/**
 * API-M019 (TASK-M077): deret mood untuk grafik di aplikasi mobile.
 *
 * Satu elemen per tanggal WIB (`entry_date`, RULE-035) berisi catatan TERAKHIR hari itu; hari tanpa
 * catatan tidak dikirim. Query lewat relasi pemilik (RULE-033). Kolom `note` (terenkripsi, RULE-032)
 * tidak pernah dipilih, jadi tidak didekripsi dan tidak mungkin ikut terkirim.
 */
final class MoodSeries
{
    /** Jendela yang diizinkan (hari), sama dengan mode grafik di web (`MoodPresenter::CHART_DAYS` = 30). */
    public const ALLOWED_DAYS = [7, 30];

    /**
     * @return array{days: list<array{date: string, mood: int, logged_at: string}>, summary: array{average: float|null, best_date: string|null, filled_days: int, total_days: int}, range: array{from: string, to: string}}
     */
    public function forUser(User $user, int $days): array
    {
        $today = Wib::now()->startOfDay();
        $start = $today->subDays($days - 1);
        $tomorrow = $today->addDay();

        // Rentang, bukan `=`: di SQLite `entry_date` tersimpan sebagai "Y-m-d 00:00:00" (cast `date`).
        $entries = $user->moodEntries()
            ->select(['id', 'mood', 'entry_date', 'logged_at'])
            ->where('entry_date', '>=', $start->format('Y-m-d'))
            ->where('entry_date', '<', $tomorrow->format('Y-m-d'))
            ->orderBy('logged_at')
            ->orderBy('id')
            ->get();

        $rows = [];
        foreach ($entries as $entry) {
            /** @var MoodEntry $entry */
            $rows[] = [
                'date' => $entry->entry_date->format('Y-m-d'),
                'mood' => $entry->mood->value,
                'logged_at' => $entry->logged_at->toImmutable()->utc()->toIso8601ZuluString(),
            ];
        }

        return $this->build($rows, $start, $today, $days);
    }

    /**
     * Murni (tanpa database), agar mudah diuji: `$rows` sudah urut dari terlama ke terbaru.
     * Baris dengan tanggal yang sama ditimpa baris berikutnya, jadi yang tersisa adalah catatan terakhir.
     *
     * @param  list<array{date: string, mood: int, logged_at: string}>  $rows
     * @return array{days: list<array{date: string, mood: int, logged_at: string}>, summary: array{average: float|null, best_date: string|null, filled_days: int, total_days: int}, range: array{from: string, to: string}}
     */
    public function build(array $rows, CarbonImmutable $start, CarbonImmutable $today, int $totalDays): array
    {
        $from = $start->format('Y-m-d');
        $to = $today->format('Y-m-d');

        /** @var array<string, array{date: string, mood: int, logged_at: string}> $lastPerDate */
        $lastPerDate = [];
        foreach ($rows as $row) {
            if ($row['date'] < $from || $row['date'] > $to) {
                continue;
            }
            $lastPerDate[$row['date']] = $row;
        }
        ksort($lastPerDate);
        $days = array_values($lastPerDate);

        $average = null;
        $bestDate = null;
        if ($days !== []) {
            $sum = 0;
            $bestMood = 0;
            foreach ($days as $day) {
                $sum += $day['mood'];
                // `>=`: bila seri, hari terbaik adalah yang paling baru (data terurut naik).
                if ($day['mood'] >= $bestMood) {
                    $bestMood = $day['mood'];
                    $bestDate = $day['date'];
                }
            }
            $average = round($sum / count($days), 2);
        }

        return [
            'days' => $days,
            'summary' => [
                'average' => $average,
                'best_date' => $bestDate,
                'filled_days' => count($days),
                'total_days' => $totalDays,
            ],
            'range' => ['from' => $from, 'to' => $to],
        ];
    }
}
