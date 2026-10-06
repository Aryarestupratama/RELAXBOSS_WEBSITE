<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

/** API-017: buat Asesmen. Slug ditentukan sekali di sini (dipakai di URL, tidak berubah lagi). */
final class StoreAssessmentRequest extends AssessmentFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('assessments', 'slug'),
            ],
            ...parent::rules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.required' => 'Slug wajib diisi.',
            'slug.max' => 'Slug maksimal 80 karakter.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung (contoh: cek-stres-harian).',
            'slug.unique' => 'Slug ini sudah dipakai Instrumen lain.',
            ...parent::messages(),
        ];
    }

    protected function existingQuestionIds(): array
    {
        return [];
    }
}
