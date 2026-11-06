<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\User;
use App\Support\Wib;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

/**
 * FR-M004: "satu aliran per hari" untuk API mobile, tanpa mengubah skema (Architecture-M 6.1).
 * Hari percakapan berganti pukul `relaxboss.mobile.day_cutoff_hour` WIB (awal 4), bukan 00.00.
 * Kuota harian (`ChatQuota`) tetap 00.00 WIB; selisihnya didokumentasikan (OQ-MA7).
 * Selalu lewat relasi pemilik (RULE-033). Percakapan dari web di jendela yang sama ikut dipakai.
 */
final class DailyConversationResolver
{
    /** Percakapan aktif milik pengguna untuk hari ini; dibuat bila belum ada. */
    public function today(User $user, ?CarbonInterface $now = null): Conversation
    {
        $existing = $this->find($user, $now);

        if ($existing !== null) {
            return $existing;
        }

        // Dua perangkat tidak boleh membuat dua percakapan. Kunci terpisah dari kunci giliran chat
        // (yang bisa dipegang sampai 90 detik selama AI menjawab).
        return Cache::lock('relaxmate:conversation:'.$user->getKey(), 10)->block(5, function () use ($user, $now): Conversation {
            return $this->find($user, $now) ?? $user->conversations()->create();
        });
    }

    /**
     * Jendela hari percakapan yang memuat $now, dalam UTC: [awal, akhir).
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function window(?CarbonInterface $now = null): array
    {
        $cutoff = max(0, min(23, (int) config('relaxboss.mobile.day_cutoff_hour')));
        $wib = $now === null ? Wib::now() : Wib::toWib($now);

        $start = $wib->subHours($cutoff)->startOfDay()->addHours($cutoff);

        return [$start->utc(), $start->addDay()->utc()];
    }

    /** Kunci hari percakapan (Y-m-d, WIB) untuk $moment: sebelum jam batas masih dihitung hari sebelumnya. */
    public function dayKey(CarbonInterface $moment): string
    {
        $cutoff = max(0, min(23, (int) config('relaxboss.mobile.day_cutoff_hour')));

        return Wib::toWib($moment)->subHours($cutoff)->format('Y-m-d');
    }

    /** True bila Percakapan dibuat di jendela hari percakapan yang memuat $now (boleh menerima pesan baru dari mobile). */
    public function isCurrent(Conversation $conversation, ?CarbonInterface $now = null): bool
    {
        [$start, $end] = $this->window($now);

        return $conversation->created_at >= $start && $conversation->created_at < $end;
    }

    private function find(User $user, ?CarbonInterface $now): ?Conversation
    {
        [$start, $end] = $this->window($now);

        return $user->conversations()
            ->where('status', ConversationStatus::Active->value)
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->latest('created_at')
            ->first();
    }
}
