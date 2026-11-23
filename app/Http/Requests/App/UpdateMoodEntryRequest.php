<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ubah Mood Entry dari Ruang tulis (TASK-053, FR-040). Catatan wajib terisi: mengosongkannya sama dengan menghapus entri
 * dari buku harian, dan untuk itu ada aksi Hapus tersendiri. Tenaga boleh dikosongkan (`null`).
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
            'mood' => ['required', 'integer', Rule::enum(MoodLevel::class)],
            'arousal_input' => ['nullable', 'string', Rule::enum(ArousalInput::class)],
            'note' => ['required', 'string', "max:{$max}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $max = (int) config('relaxboss.limits.mood_note_max_chars');

        return [
            'mood.required' => 'Pilih perasaanmu dulu, ya.',
            'mood.integer' => 'Pilihan perasaan tidak valid. Coba lagi, ya.',
            'mood.enum' => 'Pilihan perasaan tidak valid. Coba lagi, ya.',
            'arousal_input.string' => 'Pilihan tenaga tidak valid. Coba lagi, ya.',
            'arousal_input.enum' => 'Pilihan tenaga tidak valid. Coba lagi, ya.',
            'note.required' => 'Catatan tidak boleh kosong. Untuk menghapus catatan, pakai tombol Hapus.',
            'note.string' => 'Catatan tidak valid. Coba lagi, ya.',
            'note.max' => "Catatan terlalu panjang. Maksimal {$max} karakter.",
        ];
    }
}
