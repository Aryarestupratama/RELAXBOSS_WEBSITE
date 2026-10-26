<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

/** API-020. Email tidak termasuk: mengubahnya butuh verifikasi ulang (TODO-LATER). */
final class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'major', 'institution_name'] as $field) {
            if (is_string($this->input($field))) {
                $value = trim($this->input($field));
                $this->merge([$field => $value === '' && $field !== 'name' ? null : $value]);
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:100'],
            'institution_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'major.max' => 'Jurusan maksimal 100 karakter.',
            'institution_name.max' => 'Kampus maksimal 150 karakter.',
        ];
    }

    /**
     * @return array{name: string, major: ?string, institution_name: ?string}
     */
    public function profile(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'major' => $this->validated('major'),
            'institution_name' => $this->validated('institution_name'),
        ];
    }
}
