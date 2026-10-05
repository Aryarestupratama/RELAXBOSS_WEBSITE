<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\Intent;
use App\Enums\SuggestionType;

/**
 * Saran tindakan dari prioritas intent (FR-029, Architecture 6.4). Dihitung di kode; yang disimpan
 * hanya `suggestion_type`.
 *
 *  1 Darurat -> escalate_crisis (tidak bisa ditutup; hanya bila `crisis_contacts` terisi)
 *  2 Tinggi  -> recommend_professional
 *  3 Normal  -> recommend_support
 *  4 Rendah  -> tanpa saran
 */
final class ActionSuggestionService
{
    public function typeFor(?Intent $intent): ?SuggestionType
    {
        if ($intent === null) {
            return null;
        }

        return match ($intent->priority()) {
            1 => $this->hasCrisisContacts() ? SuggestionType::EscalateCrisis : null,
            2 => SuggestionType::RecommendProfessional,
            3 => SuggestionType::RecommendSupport,
            default => null,
        };
    }

    /**
     * Bentuk untuk klien. Kontak bantuan diambil dari `config('relaxboss.crisis_contacts')` oleh pemanggil.
     *
     * @return array{type: string, priority: int, dismissible: bool}|null
     */
    public function suggestion(?Intent $intent): ?array
    {
        $type = $this->typeFor($intent);

        if ($type === null || $intent === null) {
            return null;
        }

        return [
            'type' => $type->value,
            'priority' => $intent->priority(),
            'dismissible' => $type !== SuggestionType::EscalateCrisis,
        ];
    }

    private function hasCrisisContacts(): bool
    {
        $contacts = config('relaxboss.crisis_contacts');

        return is_array($contacts) && $contacts !== [];
    }
}
