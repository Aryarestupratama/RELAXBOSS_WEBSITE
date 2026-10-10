<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }

        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:'.config('relaxboss.auth.password_min_length'), 'max:255'],
            'major' => ['nullable', 'string', 'max:100'],
            'institution_name' => ['nullable', 'string', 'max:150'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $min = (int) config('relaxboss.auth.password_min_length');

        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email ini sudah terdaftar. Coba masuk, atau pakai email lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => "Kata sandi minimal {$min} karakter.",
            'password.max' => 'Kata sandi maksimal 255 karakter.',
            'major.max' => 'Jurusan maksimal 100 karakter.',
            'institution_name.max' => 'Kampus maksimal 150 karakter.',
            'terms.accepted' => 'Centang dulu bahwa kamu sudah membaca ketentuan.',
        ];
    }
}
