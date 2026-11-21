<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Mood\UpdateMoodEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateMoodEntryRequest;
use App\Services\Mood\MoodJournal;
use Illuminate\Http\JsonResponse;

/**
 * API-M021: ubah Mood Entry milik sendiri. Entri milik orang lain atau tidak ada: 404 (RULE-033).
 */
final class MoodEntryUpdateController extends Controller
{
    public function __invoke(
        UpdateMoodEntryRequest $request,
        string $entry,
        UpdateMoodEntry $action,
        MoodJournal $journal,
    ): JsonResponse {
        $model = $request->user()->moodEntries()->findOrFail($entry);

        $updated = $action->handle($model, $request->validated());

        return response()->json(['data' => ['entry' => $journal->entry($updated)]]);
    }
}
