<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * API-M021. Kirim hanya kolom yang diubah. `arousal_input` dan `note` boleh `null` untuk mengosongkan.
 * Sedikitnya satu kolom wajib ada.
 */
final class UpdateMoodEntryRequest extends FormRequest
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
            'mood' => ['sometimes', 'required', 'integer', Rule::enum(MoodLevel::class)],
            'arousal_input' => ['sometimes', 'nullable', 'string', Rule::enum(ArousalInput::class)],
            'note' => ['sometimes', 'nullable', 'string', "max:{$max}"],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->hasAny(['mood', 'arousal_input', 'note'])) {
                    $validator->errors()->add('entry', 'Tidak ada yang diubah.');
                }
            },
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
