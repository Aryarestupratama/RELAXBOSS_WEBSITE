<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API-M016: baca pesan satu Percakapan (hanya baca), urut lama ke baru, 30 per halaman berkursor.
 * Percakapan milik orang lain atau tidak ada: 404 (RULE-033). Isi pesan tidak pernah masuk log (RULE-040).
 */
final class MessagesController extends Controller
{
    private const PER_PAGE = 30;

    public function __invoke(Request $request, string $conversation, ChatPresenter $presenter): JsonResponse
    {
        $model = $request->user()->conversations()->findOrFail($conversation);

        // `id` pesan naik sesuai waktu simpan dan unik, jadi cocok sebagai kunci kursor.
        $page = $model->messages()
            ->reorder()
            ->orderBy('id')
            ->cursorPaginate(self::PER_PAGE);

        return response()->json(['data' => [
            'conversation' => [
                'id' => $model->id,
                'has_crisis' => $model->has_crisis,
                'label' => $presenter->dateLabel($model),
            ],
            'messages' => $page->getCollection()
                ->map(fn ($message): array => $presenter->apiMessage($message))
                ->values(),
            'next_cursor' => $page->nextCursor()?->encode(),
        ]]);
    }
}
