<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Mood\CreateMoodEntry;
use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMoodEntryRequest;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;

/**
 * API-M018: buat Mood Entry dari aplikasi mobile (Cek Rasa, FR-M013, OQ-M3).
 * Logika dan batas harian ada di Action `CreateMoodEntry` yang sama dengan web (API-011).
 * Entri selalu milik pemilik token (RULE-033). Catatan tidak pernah dikembalikan atau dicatat di log.
 */
final class MoodEntryController extends Controller
{
    public function __invoke(StoreMoodEntryRequest $request, CreateMoodEntry $action): JsonResponse
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

            return ApiError::response(
                'mood_limit_reached',
                "Kamu sudah mencatat {$limit} kali hari ini. Besok kamu bisa mencatat lagi.",
                429,
            );
        }

        return response()->json(['data' => ['entry' => [
            'id' => $entry->id,
            'mood' => $entry->mood->value,
            'mood_label' => $entry->mood->label(),
            'created_at' => $entry->logged_at->toImmutable()->utc()->toIso8601ZuluString(),
        ]]], 201);
    }
}
