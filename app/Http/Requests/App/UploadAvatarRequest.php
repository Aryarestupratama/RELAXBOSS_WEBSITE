<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

/** API-022. */
final class UploadAvatarRequest extends FormRequest
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
        $pixels = (int) config('relaxboss.avatar.max_pixels');

        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:'.implode(',', (array) config('relaxboss.avatar.mimes')),
                'max:'.(int) config('relaxboss.avatar.max_kb'),
                "dimensions:max_width={$pixels},max_height={$pixels}",
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $kb = (int) config('relaxboss.avatar.max_kb');
        $pixels = (int) config('relaxboss.avatar.max_pixels');
        $mb = round($kb / 1024, 1);

        return [
            'avatar.required' => 'Pilih foto dulu.',
            'avatar.file' => 'Berkas tidak valid.',
            'avatar.uploaded' => "Foto gagal diunggah. Ukuran maksimal {$mb} MB.",
            'avatar.image' => 'Berkas harus berupa gambar.',
            'avatar.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'avatar.max' => "Ukuran foto maksimal {$mb} MB.",
            'avatar.dimensions' => "Foto terlalu besar. Maksimal {$pixels} x {$pixels} piksel.",
        ];
    }
}
