<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** API-M020. `before` = `id` entri terakhir yang sudah diterima klien (kursor); kosong = halaman pertama. */
final class IndexMoodJournalRequest extends FormRequest
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
            'before' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'before.integer' => 'Penanda halaman tidak valid.',
            'before.min' => 'Penanda halaman tidak valid.',
        ];
    }

    public function before(): ?int
    {
        $value = $this->validated('before');

        return $value === null ? null : (int) $value;
    }
}
