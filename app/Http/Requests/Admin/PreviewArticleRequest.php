<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** API-024: isi Markdown yang akan dipratinjau. */
final class PreviewArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'body_markdown' => ['required', 'string', 'max:'.(int) config('relaxboss.admin.articles.body_max_chars')],
        ];
    }
}
