<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\NotificationKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Hanya kunci nudge yang boleh ditutup yang diterima (pengingat mood dan pengumuman tidak). */
final class DismissNudgeRequest extends FormRequest
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
            'kind' => ['required', 'string', Rule::in(NotificationKind::dismissibleValues())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kind.required' => 'Notifikasi tidak dikenali. Muat ulang halaman lalu coba lagi.',
            'kind.string' => 'Notifikasi tidak dikenali. Muat ulang halaman lalu coba lagi.',
            'kind.in' => 'Notifikasi ini tidak bisa ditutup.',
        ];
    }
}
