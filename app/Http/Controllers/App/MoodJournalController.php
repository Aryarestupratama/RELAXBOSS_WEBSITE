<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Mood\UpdateMoodEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexMoodJournalRequest;
use App\Http\Requests\App\UpdateMoodEntryRequest;
use App\Services\Mood\MoodJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-030 Buku harian dan ubah-hapus catatan di web (TASK-053, FR-011, FR-040). Memakai `MoodJournal` dan `UpdateMoodEntry`
 * yang sama dengan API mobile (API-M020 sampai M022). Entri dicari lewat relasi pemilik (RULE-033): milik orang lain atau
 * tidak ada = 404. Isi catatan hanya dikirim ke pemiliknya dan tidak pernah masuk log (RULE-040).
 */
final class MoodJournalController extends Controller
{
    public function index(IndexMoodJournalRequest $request, MoodJournal $journal): Response
    {
        return Inertia::render('App/Mood/Journal', $journal->page(
            $request->user(),
            $request->before(),
            (int) config('relaxboss.limits.mood_journal_per_page'),
        ));
    }

    /** Ruang tulis dalam mode ubah: kertas berisi catatan yang ada. Mengubah tidak memakai jatah catatan harian. */
    public function edit(Request $request, string $entry, MoodJournal $journal): Response
    {
        $model = $request->user()->moodEntries()->findOrFail($entry);
        $limit = (int) config('relaxboss.limits.mood_per_day');

        return Inertia::render('App/Mood/Write', [
            'limit' => $limit,
            'remaining' => $limit,
            'note_max_chars' => (int) config('relaxboss.limits.mood_note_max_chars'),
            'initial_mood' => null,
            'initial_arousal' => null,
            'editing' => $journal->entry($model),
        ]);
    }

    public function update(UpdateMoodEntryRequest $request, string $entry, UpdateMoodEntry $action): RedirectResponse
    {
        $model = $request->user()->moodEntries()->findOrFail($entry);

        $action->handle($model, $request->validated());

        return redirect()->route('app.mood.journal')->with('status', 'mood-updated');
    }

    /** Hapus permanen; mengurangi hitungan batas harian dan menggeser grafik (sama dengan API-M022). */
    public function destroy(Request $request, string $entry): RedirectResponse
    {
        $request->user()->moodEntries()->findOrFail($entry)->delete();

        return redirect()->route('app.mood.journal')->with('status', 'mood-deleted');
    }
}
