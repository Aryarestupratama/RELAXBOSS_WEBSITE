<?php

declare(strict_types=1);

namespace App\Actions\Consent;

use App\Models\User;

/**
 * API-012: catat consent AI versi terbaru (RelaxMate dan Rekomendasi AI, A-5).
 * Idempoten: bila consent versi terbaru sudah ada, waktu aslinya dipertahankan.
 * Kolom consent tidak ada di `$fillable`, jadi diisi eksplisit lewat `forceFill`.
 */
final class GrantAiConsent
{
    public function handle(User $user): void
    {
        if ($user->hasAiConsent()) {
            return;
        }

        $user->forceFill([
            'ai_consent_at' => now()->utc(),
            'ai_consent_version' => (int) config('relaxboss.ai_consent_version'),
        ])->save();
    }
}
