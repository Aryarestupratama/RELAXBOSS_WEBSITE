<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Mood\CreateMoodEntry;
use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreMoodEntryRequest;
use App\Services\Mood\MoodPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Mood Tracker. Semua data lewat relasi pemilik (RULE-033). */
final class MoodController extends Controller
{
    /** SCR-016: input, entri hari ini, grafik 7 dan 30 hari (FR-011, FR-012). */
    public function index(Request $request, MoodPresenter $presenter): Response
    {
        return Inertia::render('App/Mood/Index', [
            ...$presenter->page($request->user()),
            'note_max_chars' => (int) config('relaxboss.limits.mood_note_max_chars'),
            'saved' => session('status') === 'mood-saved',
        ]);
    }

    /** API-011: buat Mood Entry. Batas harian dikembalikan sebagai galat form `mood`. */
    public function store(StoreMoodEntryRequest $request, CreateMoodEntry $action): RedirectResponse
    {
        $validated = $request->validated();

        $entry = $action->handle(
            $request->user(),
            MoodLevel::from((int) $validated['mood']),
            isset($validated['arousal_input']) ? ArousalInput::from((string) $validated['arousal_input']) : null,
            isset($validated['note']) ? (string) $validated['note'] : null,
        );

        if ($entry === null) {
            $limit = (int) config('relaxboss.limits.mood_per_day');

            return redirect()->route('app.mood.index')->withErrors([
                'mood' => "Kamu sudah mencatat {$limit} kali hari ini. Besok kamu bisa mencatat lagi.",
            ]);
        }

        return redirect()->route('app.mood.index')->with('status', 'mood-saved');
    }
}
