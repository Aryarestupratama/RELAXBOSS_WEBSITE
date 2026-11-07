<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API-M018. Aturan sama dengan `App\Http\Requests\App\StoreMoodEntryRequest` (web, API-011); beda
 * hanya pesan galat, karena aplikasi mobile tidak "memuat ulang halaman". Batas harian dicek di
 * Action `CreateMoodEntry` (RULE-023).
 */
final class StoreMoodEntryRequest extends FormRequest
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
        $max = (int) config('relaxboss.limits.mood_note_max_chars');

        return [
            'mood' => ['required', 'integer', Rule::enum(MoodLevel::class)],
            'arousal_input' => ['nullable', 'string', Rule::enum(ArousalInput::class)],
            'note' => ['nullable', 'string', "max:{$max}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $max = (int) config('relaxboss.limits.mood_note_max_chars');

        return [
            'mood.required' => 'Pilih perasaanmu dulu.',
            'mood.integer' => 'Pilihan perasaan tidak valid. Coba lagi, ya.',
            'mood.enum' => 'Pilihan perasaan tidak valid. Coba lagi, ya.',
            'arousal_input.string' => 'Pilihan tenaga tidak valid. Coba lagi, ya.',
            'arousal_input.enum' => 'Pilihan tenaga tidak valid. Coba lagi, ya.',
            'note.string' => 'Catatan tidak valid. Coba lagi, ya.',
            'note.max' => "Catatan terlalu panjang. Maksimal {$max} karakter.",
        ];
    }
}
