<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\TrainingConsentChoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** API-M010. Hanya `granted` atau `denied`; tanpa nilai awal (RULE-071). */
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
            'choice.string' => 'Pilihan tidak valid. Coba lagi, ya.',
            'choice.enum' => 'Pilihan tidak valid. Coba lagi, ya.',
        ];
    }
}
