<?php

declare(strict_types=1);

namespace App\Actions\Consent;

use App\Enums\TrainingConsentChoice;
use App\Models\User;

/**
 * API-013: catat pilihan persetujuan pelatihan. Boleh diubah kapan saja (RULE-071),
 * jadi pilihan, waktu, dan versi teks selalu menimpa yang lama.
 */
final class RecordTrainingConsent
{
    public function handle(User $user, TrainingConsentChoice $choice): void
    {
        $user->forceFill([
            'ai_training_consent_choice' => $choice,
            'ai_training_consent_at' => now()->utc(),
            'ai_training_consent_version' => (int) config('relaxboss.ai_training_consent_version'),
        ])->save();
    }
}
