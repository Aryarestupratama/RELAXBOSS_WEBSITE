<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

/** API-021. Kecocokan kata sandi saat ini diperiksa di Action `ChangeUserPassword` (bersama pembatasan percobaan). */
final class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:'.config('relaxboss.auth.password_min_length'), 'max:255', 'confirmed', 'different:current_password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $min = (int) config('relaxboss.auth.password_min_length');

        return [
            'current_password.required' => 'Masukkan kata sandimu saat ini.',
            'current_password.max' => 'Kata sandi tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => "Kata sandi baru minimal {$min} karakter.",
            'password.max' => 'Kata sandi baru maksimal 255 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang sekarang.',
        ];
    }
}
