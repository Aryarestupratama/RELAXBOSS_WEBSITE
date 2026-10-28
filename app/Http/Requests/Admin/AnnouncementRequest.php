<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ArticleStatus;
use App\Support\Wib;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Aturan buat dan ubah pengumuman (FR-038). Akses dijaga middleware `admin` pada route. */
final class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'body_markdown' => ['required', 'string', 'max:'.(int) config('relaxboss.admin.announcements.body_max_chars')],
            'status' => ['required', Rule::enum(ArticleStatus::class)],
            'published_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul maksimal 120 karakter.',
            'body_markdown.required' => 'Isi pengumuman wajib diisi.',
            'body_markdown.max' => 'Isi pengumuman terlalu panjang.',
            'status.required' => 'Status wajib dipilih.',
            'status.enum' => 'Status tidak valid.',
            'published_at.date_format' => 'Tanggal terbit tidak valid.',
        ];
    }

    /**
     * Data siap simpan. `published_at` dari isian WIB diubah ke UTC (RULE-035).
     *
     * @return array<string, mixed>
     */
    public function announcement(): array
    {
        $data = $this->safe()->only(['title', 'body_markdown', 'status']);

        $local = $this->input('published_at');
        $data['published_at'] = is_string($local) && $local !== '' ? Wib::fromLocal($local) : null;

        return $data;
    }
}
