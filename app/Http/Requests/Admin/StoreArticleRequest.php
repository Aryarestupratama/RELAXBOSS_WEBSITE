<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

/** API-022: buat artikel. Slug ditentukan sekali di sini (dipakai di URL Publik, tidak berubah lagi). */
final class StoreArticleRequest extends ArticleFormRequest
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
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('articles', 'slug'),
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
            'slug.max' => 'Slug maksimal 120 karakter.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan tanda hubung (contoh: cara-mengelola-stres).',
            'slug.unique' => 'Slug ini sudah dipakai artikel lain.',
            ...parent::messages(),
        ];
    }

    public function slug(): string
    {
        return (string) $this->validated('slug');
    }
}
