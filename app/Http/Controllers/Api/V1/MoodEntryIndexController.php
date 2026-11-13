<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexMoodEntriesRequest;
use App\Services\Mood\MoodSeries;
use Illuminate\Http\JsonResponse;

/**
 * API-M019: deret mood 7 atau 30 hari untuk grafik di aplikasi mobile (TASK-M077, FR-M030).
 * Hanya milik pemilik token (RULE-033). Catatan (`note`) tidak pernah dikirim.
 */
final class MoodEntryIndexController extends Controller
{
    public function __invoke(IndexMoodEntriesRequest $request, MoodSeries $series): JsonResponse
    {
        return response()->json(['data' => $series->forUser($request->user(), $request->days())]);
    }
}
