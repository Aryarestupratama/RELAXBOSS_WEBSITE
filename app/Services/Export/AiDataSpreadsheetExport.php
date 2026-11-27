<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\DTOs\ExportedSpreadsheet;
use App\DTOs\ExportOptions;
use App\Exceptions\SpreadsheetExportException;
use App\Support\Wib;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Merakit satu berkas Excel dari satu atau lebih eksportir AI (TASK-057, FR-030).
 *
 * Data memakai `query()` dan `toRecord()` eksportir, jadi penjaga identitas dan consent (hanya `granted`, tanpa
 * `user_id`, kontak krisis diganti penanda) tidak diduplikasi di sini (RULE-070, RULE-072). ID record diacak per ekspor.
 * Berkas ditulis bertahap ke folder non-publik `storage/app/private/exports`; pemanggil wajib menghapusnya setelah dikirim.
 * Log hanya fitur, jumlah record, dan durasi (RULE-040).
 */
final class AiDataSpreadsheetExport
{
    public const INFO_SHEET = 'Info';

    private const FEATURE_LABELS = ['chat' => 'Chatbot (RelaxMate)', 'assessment' => 'Asesmen'];

    public function __construct(private readonly AiDataExporterRegistry $registry) {}

    /**
     * @param  list<string>  $features  nama fitur yang terdaftar dan mendukung Excel
     * @return ExportedSpreadsheet|null null bila tidak ada record sama sekali (tidak ada berkas tertinggal)
     *
     * @throws SpreadsheetExportException
     */
    public function build(array $features, ExportOptions $options): ?ExportedSpreadsheet
    {
        $exporters = [];

        foreach (array_values(array_unique($features)) as $feature) {
            $exporter = $this->registry->findSpreadsheet($feature);

            if ($exporter === null) {
                throw new InvalidArgumentException("Fitur \"{$feature}\" tidak mendukung ekspor Excel.");
            }

            $exporters[$feature] = $exporter;
        }

        if ($exporters === []) {
            throw new InvalidArgumentException('Pilih minimal satu fitur.');
        }

        $directory = $this->directory();
        $path = $directory.DIRECTORY_SEPARATOR.'xlsx-'.bin2hex(random_bytes(8)).'.xlsx';
        $writer = new XlsxWriter($path, $directory);
        $startedAt = hrtime(true);

        $records = array_fill_keys(array_keys($exporters), 0);
        $rows = [];
        $unreadable = 0;
        $usedIds = [];

        try {
            foreach ($exporters as $exporter) {
                foreach ($exporter->sheets() as $sheet => $columns) {
                    $writer->addSheet($sheet, $columns);
                    $rows[$sheet] = 0;
                }
            }

            foreach ($exporters as $feature => $exporter) {
                foreach ($exporter->query($options) as $row) {
                    try {
                        $record = $exporter->toRecord($row);
                    } catch (DecryptException) {
                        // Isi tidak bisa dibaca (mis. APP_KEY berubah): lewati, jangan hentikan seluruh ekspor.
                        $unreadable++;

                        continue;
                    }

                    do {
                        $id = bin2hex(random_bytes(8));
                    } while (isset($usedIds[$id]));
                    $usedIds[$id] = true;

                    foreach ($exporter->rows($id, $record) as $sheet => $sheetRows) {
                        foreach ($sheetRows as $cells) {
                            $writer->addRow($sheet, $cells);
                            $rows[$sheet]++;
                        }
                    }

                    $records[$feature]++;
                }
            }

            if (array_sum($records) === 0) {
                $writer->abort();

                return null;
            }

            $writer->addSheet(self::INFO_SHEET, ['Keterangan' => 34, 'Nilai' => 100]);

            foreach ($this->infoRows(array_keys($exporters), $options, $records, $rows, $unreadable) as $infoRow) {
                $writer->addRow(self::INFO_SHEET, $infoRow);
            }

            $writer->finish();
        } catch (Throwable $e) {
            $writer->abort();

            // Hanya nama kelas galat yang dicatat, tidak pernah isi data (RULE-040).
            Log::error('ai_export_xlsx_failed', ['features' => implode(',', array_keys($exporters)), 'error' => $e::class]);

            throw $e;
        }

        Log::info('ai_export_xlsx', [
            'features' => implode(',', array_keys($exporters)),
            'records' => array_sum($records),
            'duration_ms' => (int) ((hrtime(true) - $startedAt) / 1_000_000),
        ]);

        return new ExportedSpreadsheet($path, $records, $rows, $unreadable);
    }

    /** Folder non-publik untuk berkas ekspor (sama dengan perintah Artisan; sudah ada di `.gitignore`). */
    private function directory(): string
    {
        $directory = storage_path('app/private/exports');

        if (! is_dir($directory) && ! @mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Folder ekspor tidak bisa dibuat.');
        }

        return $directory;
    }

    /**
     * @param  list<string>  $features
     * @param  array<string, int>  $records
     * @param  array<string, int>  $rows
     * @return list<list<string|int>>
     */
    private function infoRows(array $features, ExportOptions $options, array $records, array $rows, int $unreadable): array
    {
        $labels = array_map(static fn (string $feature): string => self::FEATURE_LABELS[$feature] ?? $feature, $features);

        $range = 'Semua data';
        if ($options->since !== null && $options->until !== null) {
            $range = Wib::toWib($options->since)->format('Y-m-d').' s.d. '.Wib::toWib($options->until)->subDay()->format('Y-m-d')
                .' (WIB; tanggal percakapan dibuat atau hasil Asesmen selesai)';
        }

        $info = [
            ['Dibuat (WIB)', Wib::now()->format('Y-m-d H:i')],
            ['Dataset', implode(', ', $labels)],
            ['Rentang tanggal', $range],
        ];

        foreach ($records as $feature => $count) {
            $info[] = ['Jumlah record: '.(self::FEATURE_LABELS[$feature] ?? $feature), $count];
        }

        foreach ($rows as $sheet => $count) {
            $info[] = ['Jumlah baris: '.$sheet, $count];
        }

        if ($unreadable > 0) {
            $info[] = ['Dilewati (isi tidak terbaca)', $unreadable];
        }

        return [...$info, ...[
            ['Sumber', 'Hanya pengguna yang menjawab "Ya" pada persetujuan penggunaan data untuk pelatihan.'],
            ['Identitas', 'Tanpa nama, email, ID pengguna, jurusan, atau kampus. ID di berkas ini acak, hanya berlaku di berkas ini, dan tidak bisa ditelusuri ke pengguna. Kontak bantuan diganti [KONTAK_BANTUAN].'],
            ['Krisis', 'Percakapan dan hasil krisis ikut diekspor; tandanya ada di kolom is_crisis.'],
            ['Tanggal', 'Isi lembar sengaja tanpa tanggal dan jam.'],
            ['Perlakuan berkas', 'Berisi isi sensitif. Jangan di-commit, jangan dibagikan, tinjau manual sebelum dipakai melatih model (RULE-073), dan hapus setelah dipakai.'],
        ]];
    }
}
