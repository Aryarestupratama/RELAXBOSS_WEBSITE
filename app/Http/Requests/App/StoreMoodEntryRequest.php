<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** API-011. Validasi di server (RULE-023); batas harian dicek di `CreateMoodEntry`. */
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
            'mood.integer' => 'Pilihan perasaan tidak valid. Muat ulang halaman lalu coba lagi.',
            'mood.enum' => 'Pilihan perasaan tidak valid. Muat ulang halaman lalu coba lagi.',
            'arousal_input.string' => 'Pilihan tenaga tidak valid. Muat ulang halaman lalu coba lagi.',
            'arousal_input.enum' => 'Pilihan tenaga tidak valid. Muat ulang halaman lalu coba lagi.',
            'note.string' => 'Catatan tidak valid. Muat ulang halaman lalu coba lagi.',
            'note.max' => "Catatan terlalu panjang. Maksimal {$max} karakter.",
        ];
    }
}
