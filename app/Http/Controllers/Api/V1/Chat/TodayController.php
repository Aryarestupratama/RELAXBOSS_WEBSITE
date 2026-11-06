<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatQuota;
use App\Services\Chat\DailyConversationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API-M012: ambil atau buat Percakapan hari ini beserta seluruh pesannya (satu hari paling banyak
 * `chat_per_day` pesan pengguna, jadi satu halaman cukup). Hanya milik pemilik token (RULE-033);
 * isi pesan tidak pernah masuk log (RULE-040).
 */
final class TodayController extends Controller
{
    public function __invoke(
        Request $request,
        DailyConversationResolver $resolver,
        ChatPresenter $presenter,
        ChatQuota $quota,
    ): JsonResponse {
        $user = $request->user();
        $conversation = $resolver->today($user);

        return response()->json(['data' => [
            'conversation' => [
                'id' => $conversation->id,
                'has_crisis' => $conversation->has_crisis,
                'day' => $resolver->dayKey($conversation->created_at),
                'created_at' => $conversation->created_at->toImmutable()->utc()->toIso8601ZuluString(),
            ],
            'messages' => $conversation->messages()->get()
                ->map(fn ($message): array => $presenter->apiMessage($message))
                ->values(),
            'unanswered' => $presenter->hasUnanswered($conversation),
            'limits' => [
                'message_max_chars' => (int) config('relaxboss.limits.message_max_chars'),
                'per_day' => $quota->limit(),
                'remaining_today' => $quota->remainingToday($user),
            ],
        ]]);
    }
}
