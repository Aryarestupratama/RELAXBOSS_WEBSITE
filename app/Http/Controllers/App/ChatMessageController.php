<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Chat\SendChatMessage;
use App\Exceptions\ChatTurnRejectedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreChatMessageRequest;
use App\Services\Chat\ChatPresenter;
use Illuminate\Http\JsonResponse;

/**
 * API-015: kirim pesan, terima balasan (JSON). Consent dan batas per menit dipasang di route.
 * Logika giliran chat ada di `SendChatMessage` (RULE-022); di sini hanya kepemilikan (RULE-033)
 * dan bentuk respons. Isi pesan tidak pernah masuk log (RULE-040).
 */
final class ChatMessageController extends Controller
{
    public function store(
        StoreChatMessageRequest $request,
        string $conversation,
        SendChatMessage $send,
        ChatPresenter $presenter,
    ): JsonResponse {
        $user = $request->user();
        $model = $user->conversations()->findOrFail($conversation);

        try {
            $result = $send->handle(
                $user,
                $model,
                $request->isRetry() ? null : (string) $request->validated('content'),
                $request->isRetry(),
            );
        } catch (ChatTurnRejectedException $e) {
            return response()->json(['error' => ['code' => $e->errorCode, 'message' => $e->getMessage()]], $e->status);
        }

        return response()->json(['data' => [
            'user_message' => $presenter->message($result->userMessage),
            'assistant_message' => $presenter->message($result->assistantMessage),
            'has_crisis' => $result->hasCrisis,
            'remaining_today' => $result->remainingToday,
        ]]);
    }
}
