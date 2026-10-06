<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Models\User;
use App\Support\Wib;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Batas pembuatan Rekomendasi AI per pengguna per hari WIB (`limits.assessment_ai_per_day`).
 * Hanya pembuatan yang berhasil yang dihitung; gagal tidak mengurangi jatah.
 * Disimpan di cache (tanpa tabel baru) dan reset pada pergantian hari WIB.
 */
final class RecommendationQuota
{
    public function limit(): int
    {
        return (int) config('relaxboss.limits.assessment_ai_per_day');
    }

    public function exhausted(User $user): bool
    {
        return RateLimiter::tooManyAttempts($this->key($user), $this->limit());
    }

    public function consume(User $user): void
    {
        $secondsLeft = max(60, (int) Wib::now()->diffInSeconds(Wib::startOfDay()->addDay(), true));

        RateLimiter::hit($this->key($user), $secondsLeft);
    }

    private function key(User $user): string
    {
        return 'assessment-ai:'.$user->getKey().':'.Wib::date();
    }
}
