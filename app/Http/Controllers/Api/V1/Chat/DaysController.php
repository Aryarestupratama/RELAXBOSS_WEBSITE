<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\DailyConversationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API-M015: riwayat, Percakapan yang sudah berisi pesan, terbaru dulu, halaman berkursor.
 * Satu baris per Percakapan dengan kunci hari (`day`); klien mengelompokkan per hari.
 * Hanya milik pemilik token (RULE-033).
 */
final class DaysController extends Controller
{
    public function __invoke(Request $request, DailyConversationResolver $resolver, ChatPresenter $presenter): JsonResponse
    {
        $page = $request->user()->conversations()
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate((int) config('relaxboss.limits.history_per_page'));

        return response()->json(['data' => [
            'days' => $page->getCollection()->map(fn (Conversation $conversation): array => [
                'id' => $conversation->id,
                'day' => $resolver->dayKey($conversation->created_at),
                'label' => $presenter->dateLabel($conversation),
                'total_turns' => $conversation->total_turns,
                'has_crisis' => $conversation->has_crisis,
            ])->values(),
            'next_cursor' => $page->nextCursor()?->encode(),
        ]]);
    }
}
