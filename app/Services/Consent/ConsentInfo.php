<?php

declare(strict_types=1);

namespace App\Services\Consent;

use App\Models\User;

/**
 * Sumber tunggal teks dan versi consent (OQ-MA8, RULE-049): teks ada di `config/relaxboss.php` bagian `consent`.
 * Dipakai prop bersama web (ConsentDialog, halaman Akun) dan API mobile (`GET /consent`, `GET /me`).
 */
final class ConsentInfo
{
    /**
     * @return array{ai_title: string, ai_text: string, training_question: string, training_text: string}
     */
    public function texts(): array
    {
        $texts = (array) config('relaxboss.consent');

        return [
            'ai_title' => (string) ($texts['ai_title'] ?? ''),
            'ai_text' => (string) ($texts['ai_text'] ?? ''),
            'training_question' => (string) ($texts['training_question'] ?? ''),
            'training_text' => (string) ($texts['training_text'] ?? ''),
        ];
    }

    /**
     * @return array{ai: int, training: int}
     */
    public function requiredVersions(): array
    {
        return [
            'ai' => (int) config('relaxboss.ai_consent_version'),
            'training' => (int) config('relaxboss.ai_training_consent_version'),
        ];
    }

    /**
     * Status consent milik pengguna sendiri untuk API mobile.
     *
     * @return array{
     *     ai: array{granted: bool, accepted_version: ?int, required_version: int},
     *     training: array{choice: ?string, answered: bool, accepted_version: ?int, required_version: int},
     *     complete: bool
     * }
     */
    public function state(User $user): array
    {
        $required = $this->requiredVersions();

        return [
            'ai' => [
                'granted' => $user->hasAiConsent(),
                'accepted_version' => $user->ai_consent_at !== null ? $user->ai_consent_version : null,
                'required_version' => $required['ai'],
            ],
            'training' => [
                'choice' => $user->ai_training_consent_choice?->value,
                'answered' => $user->hasTrainingChoice(),
                'accepted_version' => $user->ai_training_consent_choice !== null ? $user->ai_training_consent_version : null,
                'required_version' => $required['training'],
            ],
            'complete' => $user->hasCompletedAiConsent(),
        ];
    }
}
