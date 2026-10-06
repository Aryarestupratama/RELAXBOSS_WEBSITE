<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

/** API-016. Kata sandi diperiksa di Action `DeleteUserAccount` (bersama pembatasan percobaan). */
final class DeleteAccountRequest extends FormRequest
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
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Masukkan kata sandimu untuk menghapus akun.',
            'password.string' => 'Kata sandi tidak valid.',
            'password.max' => 'Kata sandi tidak valid.',
        ];
    }
}
