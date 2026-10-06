<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\MessageRole;
use App\Exceptions\AiUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreChatMessageRequest;
use App\Services\Chat\ChatbotService;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatQuota;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * API-015: kirim pesan, terima balasan (JSON). Consent dan batas per menit dipasang di route.
 * Di sini: kepemilikan (RULE-033), kunci per pengguna, batas harian, lalu `ChatbotService`.
 * Isi pesan tidak pernah masuk log (RULE-040).
 */
final class ChatMessageController extends Controller
{
    public function store(
        StoreChatMessageRequest $request,
        string $conversation,
        ChatbotService $chat,
        ChatPresenter $presenter,
        ChatQuota $quota,
    ): JsonResponse {
        $user = $request->user();
        $model = $user->conversations()->findOrFail($conversation);

        // Dua pesan dari pengguna yang sama tidak diproses bersamaan (Architecture 6.3).
        $lock = Cache::lock('relaxmate:user:'.$user->getKey(), 90);

        if (! $lock->get()) {
            return $this->error('busy', 'Pesan sebelumnya masih diproses. Tunggu sebentar, ya.', 429);
        }

        try {
            try {
                if ($request->isRetry()) {
                    $last = $model->messages()->reorder()->orderByDesc('turn_number')->first();

                    if ($last === null || $last->role !== MessageRole::User) {
                        return $this->error('nothing_to_retry', 'Tidak ada pesan yang perlu dikirim ulang. Muat ulang halaman, ya.', 409);
                    }

                    // Mengulang balasan untuk pesan yang sudah tersimpan: tidak menambah hitungan harian.
                    $result = $chat->respond($user, $model, $last);
                } else {
                    if ($quota->remainingToday($user) <= 0) {
                        return $this->error(
                            'daily_limit',
                            'Kamu sudah mencapai batas percakapan hari ini. Besok kamu bisa lanjut bercerita. Kalau kamu butuh bantuan sekarang, lihat halaman Konsultasi Profesional.',
                            429,
                        );
                    }

                    // handle() menjalankan CrisisDetector sebelum AI (FR-018), lalu menyimpan pesan.
                    $result = $chat->handle($user, $model, (string) $request->validated('content'));
                }
            } catch (AiUnavailableException) {
                // Pesan pengguna sudah tersimpan (FR-017). Alasan teknis tidak ditampilkan.
                return $this->error(
                    'ai_unavailable',
                    'RelaxMate sedang tidak bisa membalas. Pesanmu tetap tersimpan. Coba lagi sebentar lagi.',
                    503,
                );
            }

            $model->refresh();

            return response()->json(['data' => [
                'user_message' => $presenter->message($result->userMessage),
                'assistant_message' => $presenter->message($result->assistantMessage),
                'has_crisis' => $model->has_crisis,
                'remaining_today' => $quota->remainingToday($user),
            ]]);
        } finally {
            $lock->release();
        }
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json(['error' => ['code' => $code, 'message' => $message]], $status);
    }
}
