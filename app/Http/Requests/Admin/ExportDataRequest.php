<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\ExportOptions;
use App\Services\Export\AiDataExporterRegistry;
use App\Support\Wib;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ekspor Excel data AI (TASK-057). Hanya dua pilihan waktu: semua data atau rentang tanggal (WIB, termasuk
 * tanggal akhir). Dataset `all` berarti semua fitur yang mendukung Excel.
 */
final class ExportDataRequest extends FormRequest
{
    public const DATASET_ALL = 'all';

    public const RANGE_ALL = 'all';

    public const RANGE_CUSTOM = 'custom';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $datasets = [...app(AiDataExporterRegistry::class)->spreadsheetFeatures(), self::DATASET_ALL];

        return [
            'dataset' => ['required', 'string', Rule::in($datasets)],
            'range' => ['required', 'string', Rule::in([self::RANGE_ALL, self::RANGE_CUSTOM])],
            'from' => ['nullable', 'required_if:range,'.self::RANGE_CUSTOM, 'date_format:Y-m-d'],
            'to' => ['nullable', 'required_if:range,'.self::RANGE_CUSTOM, 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dataset.required' => 'Pilih dataset yang mau diekspor.',
            'dataset.in' => 'Dataset tidak dikenal. Muat ulang halaman lalu coba lagi.',
            'range.required' => 'Pilih semua data atau rentang tanggal.',
            'range.in' => 'Pilihan waktu tidak valid. Muat ulang halaman lalu coba lagi.',
            'from.required_if' => 'Isi tanggal mulai.',
            'from.date_format' => 'Tanggal mulai tidak valid.',
            'to.required_if' => 'Isi tanggal akhir.',
            'to.date_format' => 'Tanggal akhir tidak valid.',
            'to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal mulai.',
        ];
    }

    /** @return list<string> Fitur yang akan diekspor. */
    public function features(): array
    {
        $dataset = (string) $this->input('dataset');

        return $dataset === self::DATASET_ALL
            ? app(AiDataExporterRegistry::class)->spreadsheetFeatures()
            : [$dataset];
    }

    /** Tanggal dibaca sebagai WIB lalu diubah ke UTC karena database menyimpan UTC (RULE-035). */
    public function options(): ExportOptions
    {
        if ($this->input('range') !== self::RANGE_CUSTOM) {
            return new ExportOptions;
        }

        $from = Wib::startOfDay(CarbonImmutable::parse((string) $this->input('from'), Wib::TIMEZONE))->setTimezone('UTC');
        // Batas atas tidak termasuk: awal hari setelah tanggal akhir.
        $until = Wib::startOfDay(CarbonImmutable::parse((string) $this->input('to'), Wib::TIMEZONE))->addDay()->setTimezone('UTC');

        return new ExportOptions(since: $from, until: $until);
    }
}
