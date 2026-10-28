<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ArticleCategory;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Parameter daftar artikel Publik (`/artikel?kategori=&q=`). FR-021, SCR-024.
 * Halaman GET publik: nilai tidak valid (array, kategori tidak dikenal, terlalu panjang) tidak
 * menggagalkan permintaan, melainkan dinormalkan atau diabaikan di server (RULE-023).
 */
class PublicArticleIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function category(): ?ArticleCategory
    {
        $value = $this->query('kategori');

        return is_string($value) ? ArticleCategory::tryFrom($value) : null;
    }

    /** Kata kunci pencarian yang sudah dibersihkan; string kosong berarti tidak mencari. */
    public function searchTerm(): string
    {
        $value = $this->query('q');

        if (! is_string($value)) {
            return '';
        }

        // Karakter kontrol menjadi spasi, spasi berulang dipadatkan, lalu dipotong sesuai batas config (RULE-026).
        $clean = preg_replace('/[\p{C}\s]+/u', ' ', $value) ?? '';

        return mb_substr(trim($clean), 0, (int) config('relaxboss.limits.articles_search_max_chars'));
    }
}
