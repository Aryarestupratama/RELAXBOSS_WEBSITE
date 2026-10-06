<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\TrainingConsentChoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** API-013. Hanya `granted` atau `denied`; tidak ada nilai awal (RULE-071). */
final class StoreTrainingConsentRequest extends FormRequest
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
            'choice' => ['required', 'string', Rule::enum(TrainingConsentChoice::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'choice.required' => 'Pilih salah satu dulu.',
            'choice.string' => 'Pilihan tidak valid. Muat ulang halaman lalu coba lagi.',
            'choice.enum' => 'Pilihan tidak valid. Muat ulang halaman lalu coba lagi.',
        ];
    }
}
