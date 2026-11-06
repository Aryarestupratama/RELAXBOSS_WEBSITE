<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\DTOs\SendChatMessageResult;
use App\Enums\MessageRole;
use App\Exceptions\AiUnavailableException;
use App\Exceptions\ChatTurnRejectedException;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Chat\ChatbotService;
use App\Services\Chat\ChatQuota;
use Illuminate\Support\Facades\Cache;

/**
 * API-015: satu giliran chat (kirim pesan baru, atau ulang balasan untuk pesan terakhir).
 * Dipakai web dan API mobile. Pemanggil sudah memastikan kepemilikan Percakapan (RULE-033),
 * consent, dan batas per menit. Di sini: kunci per pengguna (Architecture 6.3), batas harian,
 * lalu `ChatbotService`. Isi pesan tidak pernah masuk log (RULE-040).
 *
 * @throws ChatTurnRejectedException `busy`, `daily_limit`, `nothing_to_retry`, `ai_unavailable`
 */
final class SendChatMessage
{
    public function __construct(
        private readonly ChatbotService $chat,
        private readonly ChatQuota $quota,
    ) {}

    public function handle(User $user, Conversation $conversation, ?string $content, bool $retry = false): SendChatMessageResult
    {
        // Dua pesan dari pengguna yang sama tidak diproses bersamaan.
        $lock = Cache::lock('relaxmate:user:'.$user->getKey(), 90);

        if (! $lock->get()) {
            throw ChatTurnRejectedException::busy();
        }

        try {
            try {
                if ($retry) {
                    $last = $conversation->messages()->reorder()->orderByDesc('turn_number')->first();

                    if ($last === null || $last->role !== MessageRole::User) {
                        throw ChatTurnRejectedException::nothingToRetry();
                    }

                    // Mengulang balasan untuk pesan yang sudah tersimpan: tidak menambah hitungan harian.
                    $result = $this->chat->respond($user, $conversation, $last);
                } else {
                    if ($this->quota->remainingToday($user) <= 0) {
                        throw ChatTurnRejectedException::dailyLimit();
                    }

                    // handle() menjalankan CrisisDetector sebelum AI (FR-018), lalu menyimpan pesan.
                    $result = $this->chat->handle($user, $conversation, (string) $content);
                }
            } catch (AiUnavailableException) {
                // Pesan pengguna sudah tersimpan (FR-017). Alasan teknis tidak ditampilkan.
                throw ChatTurnRejectedException::aiUnavailable();
            }

            $conversation->refresh();

            return new SendChatMessageResult(
                userMessage: $result->userMessage,
                assistantMessage: $result->assistantMessage,
                hasCrisis: (bool) $conversation->has_crisis,
                remainingToday: $this->quota->remainingToday($user),
            );
        } finally {
            $lock->release();
        }
    }
}
