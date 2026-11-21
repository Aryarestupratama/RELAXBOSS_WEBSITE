<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexMoodJournalRequest;
use App\Services\Mood\MoodJournal;
use Illuminate\Http\JsonResponse;

/**
 * API-M020: buku harian (Mood Entry yang punya catatan), terbaru dulu, hanya milik pemilik token
 * (RULE-033). Respons berisi `note` terdekripsi; tidak boleh masuk log (RULE-040).
 */
final class MoodJournalController extends Controller
{
    public function __invoke(IndexMoodJournalRequest $request, MoodJournal $journal): JsonResponse
    {
        return response()->json(['data' => $journal->page(
            $request->user(),
            $request->before(),
            (int) config('relaxboss.limits.mood_journal_per_page'),
        )]);
    }
}
