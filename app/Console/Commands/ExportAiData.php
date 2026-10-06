<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DTOs\ExportOptions;
use App\Services\Export\AiDataExporterRegistry;
use App\Support\Wib;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ekspor data fitur AI untuk pelatihan di masa depan (FR-030, RULE-070 s.d. RULE-073).
 *
 * Hanya lewat Artisan (tanpa route web dan tanpa jadwal). Hanya pengguna `granted`, tanpa identitas,
 * ID record acak per ekspor, kontak krisis diganti penanda. Berkas JSONL non-publik, dibuat tanpa menimpa
 * berkas lain, dan tidak boleh masuk repo atau dipakai melatih sebelum ditinjau manual (RULE-073).
 * Log hanya berisi nama fitur, jumlah record, dan durasi (RULE-040).
 */
final class ExportAiData extends Command
{
    protected $signature = 'relaxboss:export-ai-data
        {feature : Nama fitur (chat atau assessment)}
        {--since= : Hanya data sejak tanggal ini (WIB), mis. 2026-10-01}
        {--limit= : Jumlah record maksimum}
        {--exclude-crisis : Lewati record krisis (bawaan: ikut diekspor)}
        {--output= : Path berkas JSONL (bawaan: storage/app/private/exports)}';

    protected $description = 'Ekspor data fitur AI dari pengguna yang menyetujui, tanpa identitas (FR-030)';

    public function handle(AiDataExporterRegistry $registry): int
    {
        $feature = (string) $this->argument('feature');
        $exporter = $registry->find($feature);

        if ($exporter === null) {
            $this->error("Fitur \"{$feature}\" tidak dikenal. Tersedia: ".implode(', ', $registry->features()).'.');

            return self::FAILURE;
        }

        $since = $this->parseSince($this->option('since'));
        $limit = $this->parseLimit($this->option('limit'));

        if ($since === false || $limit === false) {
            return self::FAILURE;
        }

        $path = $this->resolveOutputPath($feature, $this->option('output'));

        if ($path === null) {
            return self::FAILURE;
        }

        $excludeCrisis = (bool) $this->option('exclude-crisis');
        $options = new ExportOptions($since, $excludeCrisis);

        // Mode 'x': gagal bila berkas sudah ada, jadi ekspor lama tidak pernah tertimpa.
        $handle = @fopen($path, 'xb');

        if ($handle === false) {
            $this->error("Tidak bisa membuat berkas (mungkin sudah ada atau folder tidak bisa ditulis): {$path}");

            return self::FAILURE;
        }

        @chmod($path, 0600);

        $startedAt = hrtime(true);
        $written = 0;
        $skippedCrisis = 0;
        $skippedUnreadable = 0;
        $usedIds = [];

        try {
            foreach ($exporter->query($options) as $row) {
                if ($limit !== null && $written >= $limit) {
                    break;
                }

                try {
                    $record = $exporter->toRecord($row);
                } catch (DecryptException) {
                    // Isi tidak bisa dibaca (mis. APP_KEY berubah): lewati, jangan hentikan seluruh ekspor.
                    $skippedUnreadable++;

                    continue;
                }

                if ($excludeCrisis && ($record['is_crisis'] ?? false) === true) {
                    $skippedCrisis++;

                    continue;
                }

                unset($record['id'], $record['feature']);

                do {
                    $id = bin2hex(random_bytes(8));
                } while (isset($usedIds[$id]));
                $usedIds[$id] = true;

                $line = json_encode(
                    ['id' => $id, 'feature' => $feature] + $record,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR,
                );

                if (fwrite($handle, $line."\n") === false) {
                    throw new \RuntimeException('Gagal menulis berkas ekspor.');
                }

                $written++;
            }

            fclose($handle);
        } catch (Throwable $e) {
            // Berkas sebagian tidak dibiarkan ada. Hanya nama kelas galat yang dicatat (RULE-040).
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($path);

            Log::error('ai_export_failed', ['feature' => $feature, 'error' => $e::class]);
            $this->error('Ekspor gagal ('.$e::class.'). Berkas sebagian dihapus.');

            return self::FAILURE;
        }

        $durationMs = (int) ((hrtime(true) - $startedAt) / 1_000_000);

        Log::info('ai_export', ['feature' => $feature, 'records' => $written, 'duration_ms' => $durationMs]);

        if ($written === 0) {
            @unlink($path);
            $this->warn('Tidak ada record untuk diekspor (belum ada pengguna yang menyetujui, atau tidak ada data pada filter ini). Tidak ada berkas dibuat.');

            return self::SUCCESS;
        }

        $this->info("Selesai: {$written} record ({$feature}) dalam {$durationMs} ms.");

        if ($skippedCrisis > 0) {
            $this->line("Dilewati karena krisis (--exclude-crisis): {$skippedCrisis}.");
        }

        if ($skippedUnreadable > 0) {
            $this->warn("Dilewati karena isi tidak terbaca (dekripsi gagal): {$skippedUnreadable}.");
        }

        $this->line("Berkas: {$path}");
        $this->line('Perlakukan seperti cadangan: jangan di-commit, tinjau manual sebelum dipakai (RULE-073), hapus setelah dipakai.');

        return self::SUCCESS;
    }

    /** @return CarbonImmutable|null|false null = tidak diisi, false = tidak valid (galat sudah dicetak) */
    private function parseSince(mixed $value): CarbonImmutable|null|false
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // Tanggal dibaca sebagai waktu WIB, lalu dikonversi ke UTC karena database menyimpan UTC (RULE-035).
            return Wib::startOfDay(CarbonImmutable::parse((string) $value, Wib::TIMEZONE))->setTimezone('UTC');
        } catch (Throwable) {
            $this->error('--since tidak valid. Gunakan format tanggal, mis. 2026-10-01.');

            return false;
        }
    }

    private function parseLimit(mixed $value): int|null|false
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || ! ctype_digit($value) || (int) $value < 1) {
            $this->error('--limit harus bilangan bulat positif.');

            return false;
        }

        return (int) $value;
    }

    private function resolveOutputPath(string $feature, mixed $option): ?string
    {
        if (is_string($option) && $option !== '') {
            $path = $this->isAbsolute($option) ? $option : (getcwd() ?: '.').DIRECTORY_SEPARATOR.$option;
        } else {
            $directory = storage_path('app/private/exports');

            if (! is_dir($directory) && ! @mkdir($directory, 0700, true) && ! is_dir($directory)) {
                $this->error("Tidak bisa membuat folder ekspor: {$directory}");

                return null;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$feature.'-'.Wib::now()->format('Ymd-His').'.jsonl';
        }

        $directoryReal = realpath(dirname($path));

        if ($directoryReal === false) {
            $this->error('Folder tujuan tidak ada: '.dirname($path));

            return null;
        }

        // Berkas ekspor tidak boleh berada di folder yang bisa diakses publik.
        $public = realpath(public_path());

        if ($public !== false && ($directoryReal === $public || str_starts_with($directoryReal.DIRECTORY_SEPARATOR, $public.DIRECTORY_SEPARATOR))) {
            $this->error('Berkas ekspor tidak boleh disimpan di folder public.');

            return null;
        }

        return $directoryReal.DIRECTORY_SEPARATOR.basename($path);
    }

    private function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/') || str_starts_with($path, '\\') || (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $path);
    }
}
