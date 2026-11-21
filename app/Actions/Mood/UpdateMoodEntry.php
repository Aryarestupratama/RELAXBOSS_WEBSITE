<?php

declare(strict_types=1);

namespace App\Actions\Mood;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use App\Models\MoodEntry;

/**
 * API-M021: ubah Mood Entry milik pengguna (mood, tenaga, catatan).
 *
 * Hanya kunci yang ada di `$changes` yang diubah. `entry_date` dan `logged_at` tidak pernah berubah,
 * jadi batas harian dan grafik tidak terpengaruh. Catatan kosong disimpan sebagai null (sama dengan
 * `CreateMoodEntry`). Catatan tidak boleh masuk log (RULE-040).
 */
final class UpdateMoodEntry
{
    /**
     * @param  array{mood?: int|string, arousal_input?: string|null, note?: string|null}  $changes
     */
    public function handle(MoodEntry $entry, array $changes): MoodEntry
    {
        if (array_key_exists('mood', $changes)) {
            $entry->mood = MoodLevel::from((int) $changes['mood']);
        }

        if (array_key_exists('arousal_input', $changes)) {
            $entry->arousal_input = $changes['arousal_input'] === null
                ? null
                : ArousalInput::from((string) $changes['arousal_input']);
        }

        if (array_key_exists('note', $changes)) {
            $note = $changes['note'] === null ? null : trim((string) $changes['note']);
            $entry->note = $note === '' ? null : $note;
        }

        $entry->save();

        return $entry;
    }
}
