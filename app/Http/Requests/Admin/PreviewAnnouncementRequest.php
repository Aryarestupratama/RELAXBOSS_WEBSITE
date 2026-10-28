<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Isi Markdown pengumuman yang akan dipratinjau. */
final class PreviewAnnouncementRequest extends FormRequest
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
            'body_markdown' => ['required', 'string', 'max:'.(int) config('relaxboss.admin.announcements.body_max_chars')],
        ];
    }
}
