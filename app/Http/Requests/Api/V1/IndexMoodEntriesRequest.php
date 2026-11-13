<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Services\Mood\MoodSeries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API-M019. Parameter `days` hanya 7 atau 30 (sama dengan mode grafik di web); bila tidak dikirim, 7.
 */
final class IndexMoodEntriesRequest extends FormRequest
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
            'days' => ['nullable', 'integer', Rule::in(MoodSeries::ALLOWED_DAYS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'days.integer' => 'Rentang hari tidak valid. Pilih 7 atau 30 hari.',
            'days.in' => 'Rentang hari tidak valid. Pilih 7 atau 30 hari.',
        ];
    }

    public function days(): int
    {
        return (int) ($this->validated('days') ?? MoodSeries::ALLOWED_DAYS[0]);
    }
}
