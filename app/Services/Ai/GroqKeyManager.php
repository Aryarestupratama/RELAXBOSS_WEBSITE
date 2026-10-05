<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTOs\AiKey;
use App\Enums\AiFeature;
use Illuminate\Support\Facades\Cache;

/**
 * Pool API key Groq per fitur (ADR-009, RULE-045). Semua key milik founder.
 *
 * - Round-robin dengan penghitung di cache (`groq:index:{fitur}`).
 * - Cooldown 429 per `fitur:model:nomor key`, jadi satu key bisa cooldown di model A tetapi dipakai di model B.
 * - 401/403 menonaktifkan key 1 jam (semua model).
 * - Tidak pernah mereset cooldown atau memaksa key saat semuanya terbatas.
 * - Hanya nomor key (mulai 1) yang muncul di cache dan log.
 */
final class GroqKeyManager
{
    public const DEFAULT_COOLDOWN_SECONDS = 60;

    public const MAX_COOLDOWN_SECONDS = 3600;

    public const DISABLE_SECONDS = 3600;

    public function hasKeys(AiFeature $feature): bool
    {
        return $this->all($feature) !== [];
    }

    /**
     * Posisi awal putaran untuk satu panggilan. Dipanggil sekali per panggilan AI.
     */
    public function nextOffset(AiFeature $feature): int
    {
        $count = count($this->all($feature));

        if ($count === 0) {
            return 0;
        }

        $counter = "groq:index:{$feature->value}";
        Cache::add($counter, 0, 86400 * 365);
        $value = Cache::increment($counter);

        return (int) ($value === false ? 0 : $value) % $count;
    }

    /**
     * Key yang boleh dipakai untuk model ini, berurutan mulai dari $offset (round-robin).
     * Key yang cooldown di model ini atau sedang dinonaktifkan dilewati.
     *
     * @return list<AiKey>
     */
    public function available(AiFeature $feature, string $model, int $offset): array
    {
        $keys = $this->all($feature);
        $count = count($keys);
        $available = [];

        for ($step = 0; $step < $count; $step++) {
            $key = $keys[($offset + $step) % $count];

            if (Cache::has($this->disabledKey($feature, $key)) || Cache::has($this->cooldownKey($feature, $model, $key))) {
                continue;
            }

            $available[] = $key;
        }

        return $available;
    }

    /** 429: cooldown selama `retry-after` (cadangan 60 detik) untuk kombinasi fitur, model, dan key ini saja. */
    public function cooldown(AiFeature $feature, string $model, AiKey $key, ?int $seconds): void
    {
        $seconds = $seconds === null || $seconds < 1
            ? self::DEFAULT_COOLDOWN_SECONDS
            : min($seconds, self::MAX_COOLDOWN_SECONDS);

        Cache::put($this->cooldownKey($feature, $model, $key), true, $seconds);
    }

    /** 401/403: key bermasalah atau akun dicabut. Dinonaktifkan sementara untuk semua model. */
    public function disable(AiFeature $feature, AiKey $key): void
    {
        Cache::put($this->disabledKey($feature, $key), true, self::DISABLE_SECONDS);
    }

    /**
     * @return list<AiKey>
     */
    private function all(AiFeature $feature): array
    {
        /** @var mixed $configured */
        $configured = config("relaxboss.ai.keys.{$feature->value}", []);

        if (! is_array($configured)) {
            return [];
        }

        $keys = [];
        foreach (array_values($configured) as $index => $secret) {
            $keys[] = new AiKey($index + 1, (string) $secret);
        }

        return $keys;
    }

    private function cooldownKey(AiFeature $feature, string $model, AiKey $key): string
    {
        return "groq:cooldown:{$feature->value}:{$model}:{$key->number}";
    }

    private function disabledKey(AiFeature $feature, AiKey $key): string
    {
        return "groq:disabled:{$feature->value}:{$key->number}";
    }
}
