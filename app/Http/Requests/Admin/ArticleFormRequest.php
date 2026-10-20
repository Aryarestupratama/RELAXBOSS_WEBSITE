<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Support\Wib;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Aturan bersama API-022 (buat) dan API-023 (ubah) artikel. Akses dijaga middleware `admin` pada route. */
abstract class ArticleFormRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['required', 'string', 'max:300'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'body_markdown' => ['required', 'string', 'max:'.(int) config('relaxboss.admin.articles.body_max_chars')],
            'category' => ['required', Rule::enum(ArticleCategory::class)],
            'related_assessment_slug' => ['nullable', 'string', 'max:80', Rule::exists('assessments', 'slug')],
            'author_name' => ['required', 'string', 'max:100'],
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
            'title.max' => 'Judul maksimal 150 karakter.',
            'excerpt.required' => 'Ringkasan wajib diisi.',
            'excerpt.max' => 'Ringkasan maksimal 300 karakter.',
            'meta_title.max' => 'Judul SEO maksimal 60 karakter.',
            'meta_description.max' => 'Deskripsi SEO maksimal 160 karakter.',
            'body_markdown.required' => 'Isi artikel wajib diisi.',
            'body_markdown.max' => 'Isi artikel terlalu panjang.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.enum' => 'Kategori tidak valid.',
            'related_assessment_slug.exists' => 'Asesmen terkait tidak ditemukan.',
            'author_name.required' => 'Nama penulis wajib diisi.',
            'author_name.max' => 'Nama penulis maksimal 100 karakter.',
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
    public function article(): array
    {
        $data = $this->safe()->only([
            'title', 'excerpt', 'meta_title', 'meta_description', 'body_markdown',
            'category', 'related_assessment_slug', 'author_name', 'status',
        ]);

        $local = $this->input('published_at');
        $data['published_at'] = is_string($local) && $local !== '' ? Wib::fromLocal($local) : null;

        return $data;
    }
}
