<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Chat;

use App\Actions\Chat\SendChatMessage;
use App\Exceptions\ChatTurnRejectedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreChatMessageRequest;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\DailyConversationResolver;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;

/**
 * API-M013: kirim pesan atau ulang balasan (`retry` = true, tanpa endpoint terpisah, OQ-MA6).
 * Logika giliran ada di `SendChatMessage` (sama dengan web). Di sini: kepemilikan (RULE-033),
 * pemeriksaan bahwa Percakapan masih hari ini, dan bentuk respons.
 */
final class SendMessageController extends Controller
{
    public function __invoke(
        StoreChatMessageRequest $request,
        string $conversation,
        SendChatMessage $send,
        ChatPresenter $presenter,
        DailyConversationResolver $resolver,
    ): JsonResponse {
        $user = $request->user();
        $model = $user->conversations()->findOrFail($conversation);

        // Riwayat hari lain hanya dibaca (FR-M005). Klien yang lupa berganti hari memuat ulang `today`.
        if (! $resolver->isCurrent($model)) {
            return ApiError::response(
                'conversation_closed',
                'Hari sudah berganti. Buka percakapan hari ini untuk melanjutkan.',
                409,
            );
        }

        try {
            $result = $send->handle(
                $user,
                $model,
                $request->isRetry() ? null : (string) $request->validated('content'),
                $request->isRetry(),
            );
        } catch (ChatTurnRejectedException $e) {
            return ApiError::response($e->errorCode, $e->getMessage(), $e->status);
        }

        return response()->json(['data' => [
            'user_message' => $presenter->apiMessage($result->userMessage),
            'assistant_message' => $presenter->apiMessage($result->assistantMessage),
            'has_crisis' => $result->hasCrisis,
            'remaining_today' => $result->remainingToday,
        ]]);
    }
}
