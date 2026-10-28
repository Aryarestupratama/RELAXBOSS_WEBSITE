<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

/** FR-036. Validasi di server meskipun klien sudah membatasi pilihan jam (RULE-023). Batas jam dari config (RULE-026). */
final class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'mood_reminder_enabled' => ['required', 'boolean'],
            'mood_reminder_hour' => [
                'required',
                'integer',
                'min:'.(int) config('relaxboss.notifications.mood_hour_min'),
                'max:'.(int) config('relaxboss.notifications.mood_hour_max'),
            ],
            'nudges_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mood_reminder_enabled.required' => 'Pilihan pengingat tidak valid. Muat ulang halaman lalu coba lagi.',
            'mood_reminder_enabled.boolean' => 'Pilihan pengingat tidak valid. Muat ulang halaman lalu coba lagi.',
            'mood_reminder_hour.required' => 'Pilih jam pengingat dulu.',
            'mood_reminder_hour.integer' => 'Jam pengingat tidak valid.',
            'mood_reminder_hour.min' => 'Jam pengingat di luar pilihan yang tersedia.',
            'mood_reminder_hour.max' => 'Jam pengingat di luar pilihan yang tersedia.',
            'nudges_enabled.required' => 'Pilihan ajakan tidak valid. Muat ulang halaman lalu coba lagi.',
            'nudges_enabled.boolean' => 'Pilihan ajakan tidak valid. Muat ulang halaman lalu coba lagi.',
        ];
    }
}
