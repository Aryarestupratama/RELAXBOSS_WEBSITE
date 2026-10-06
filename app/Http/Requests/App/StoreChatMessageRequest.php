<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * API-015. Dua bentuk: pesan baru (`content`) atau ulang balasan untuk pesan terakhir yang belum
 * dijawab (`retry` = true, tanpa `content`). Galat dikembalikan sebagai JSON `{error: {code, message}}`.
 */
final class StoreChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function isRetry(): bool
    {
        return $this->boolean('retry');
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('content'))) {
            $this->merge(['content' => trim((string) $this->input('content'))]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $max = (int) config('relaxboss.limits.message_max_chars');

        return [
            'retry' => ['sometimes', 'boolean'],
            'content' => $this->boolean('retry')
                ? ['nullable']
                : ['required', 'string', "max:{$max}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $max = (int) config('relaxboss.limits.message_max_chars');

        return [
            'content.required' => 'Tulis pesanmu dulu.',
            'content.string' => 'Pesan tidak valid. Muat ulang halaman lalu coba lagi.',
            'content.max' => "Pesan terlalu panjang. Maksimal {$max} karakter.",
            'retry.boolean' => 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 'invalid_message',
                'message' => (string) $validator->errors()->first(),
            ],
        ], 422));
    }
}
