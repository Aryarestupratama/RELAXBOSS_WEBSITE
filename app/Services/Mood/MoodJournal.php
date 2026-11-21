<?php

declare(strict_types=1);

namespace App\Services\Mood;

use App\Models\MoodEntry;
use App\Models\User;

/**
 * API-M020 (TASK-M078): buku harian di aplikasi mobile.
 *
 * Hanya Mood Entry yang punya catatan, terbaru dulu, dibaca per halaman berkursor `id`
 * (`id` naik sesuai waktu simpan dan unik). Query lewat relasi pemilik (RULE-033).
 * Isi catatan hanya dikirim ke pemiliknya dan tidak pernah masuk log (RULE-040).
 */
final class MoodJournal
{
    /**
     * @return array{entries: list<array<string, mixed>>, next_before: int|null}
     */
    public function page(User $user, ?int $before, int $perPage): array
    {
        $query = $user->moodEntries()
            ->whereNotNull('note')
            ->orderByDesc('id');

        if ($before !== null) {
            $query->where('id', '<', $before);
        }

        // Ambil satu lebih untuk tahu masih ada halaman berikutnya.
        $rows = $query->limit($perPage + 1)->get();
        $hasMore = $rows->count() > $perPage;
        $page = $rows->take($perPage)->values();

        return [
            'entries' => $page->map(fn (MoodEntry $entry): array => $this->entry($entry))->all(),
            'next_before' => $hasMore ? (int) $page->last()?->id : null,
        ];
    }

    /**
     * @return array{id: int, mood: int, mood_label: string, arousal_input: string|null, arousal_label: string|null, note: string|null, entry_date: string, logged_at: string, edited: bool}
     */
    public function entry(MoodEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'mood' => $entry->mood->value,
            'mood_label' => $entry->mood->label(),
            'arousal_input' => $entry->arousal_input?->value,
            'arousal_label' => $entry->arousal_input?->label(),
            'note' => $entry->note,
            'entry_date' => $entry->entry_date->format('Y-m-d'),
            'logged_at' => $entry->logged_at->toImmutable()->utc()->toIso8601ZuluString(),
            // Ditandai diedit bila disimpan ulang lebih dari 2 detik setelah dibuat.
            'edited' => $entry->updated_at !== null
                && $entry->created_at !== null
                && $entry->updated_at->gt($entry->created_at->copy()->addSeconds(2)),
        ];
    }
}
