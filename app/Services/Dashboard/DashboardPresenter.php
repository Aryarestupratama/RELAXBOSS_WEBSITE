<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\MoodEntry;
use App\Models\User;
use App\Services\Assessment\AttemptPresenter;
use App\Support\Wib;
use Illuminate\Support\Str;

/**
 * Menyusun data Dashboard (SCR-011). Hanya dua baris terakhir yang dimuat, bukan seluruh riwayat.
 * Semua query lewat relasi pemilik (RULE-033). Catatan mood tidak ditampilkan di sini.
 */
final class DashboardPresenter
{
    private const MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function __construct(private readonly AttemptPresenter $attempts) {}

    /**
     * @return array{first_name: string, last_mood: array<string, mixed>|null, last_attempt: array<string, mixed>|null}
     */
    public function page(User $user): array
    {
        $mood = $user->moodEntries()
            ->orderByDesc('logged_at')
            ->orderByDesc('id')
            ->first();

        $attempt = $user->assessmentAttempts()
            ->with('assessment')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->first();

        return [
            'first_name' => (string) Str::of($user->name)->trim()->before(' '),
            'last_mood' => $mood === null ? null : $this->mood($mood),
            'last_attempt' => $attempt === null ? null : $this->attempts->summary($attempt),
        ];
    }

    /**
     * @return array{mood: int, mood_label: string, arousal_label: string|null, when: string}
     */
    private function mood(MoodEntry $entry): array
    {
        $moment = Wib::toWib($entry->logged_at);
        $day = Wib::date($entry->logged_at) === Wib::date()
            ? 'Hari ini'
            : $moment->day.' '.self::MONTHS[$moment->month];

        return [
            'mood' => $entry->mood->value,
            'mood_label' => $entry->mood->label(),
            'arousal_label' => $entry->arousal_input?->label(),
            'when' => $day.', '.$moment->format('H.i').' WIB',
        ];
    }
}
