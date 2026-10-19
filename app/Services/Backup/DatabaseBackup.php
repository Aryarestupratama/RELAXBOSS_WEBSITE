<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\DTOs\BackupResult;
use App\Exceptions\BackupFailedException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Cadangan database harian (Architecture bagian 12, retensi `relaxboss.backup.retention_days`).
 *
 * - MySQL atau MariaDB: `mysqldump` dialirkan langsung ke berkas gzip. Sandi dikirim lewat variabel
 *   lingkungan proses (MYSQL_PWD), bukan argumen perintah, agar tidak terlihat di daftar proses.
 * - SQLite (lokal): berkas database disalin ke gzip.
 *
 * Berkas disimpan di `storage/app/private/backups` (non-publik, tidak masuk repo, RULE-013), mode 0600.
 * Kolom sensitif sudah terenkripsi aplikasi (RULE-032); `APP_KEY` disimpan terpisah dari cadangan.
 */
final class DatabaseBackup
{
    private const PREFIX = 'relaxboss-';

    public function directory(): string
    {
        return storage_path((string) config('relaxboss.backup.path'));
    }

    /** Umur cadangan terbaru dalam jam, atau null bila belum ada cadangan. */
    public function latestAgeHours(): ?float
    {
        $newest = null;

        foreach ($this->files() as $file) {
            $time = filemtime($file);
            if ($time !== false && ($newest === null || $time > $newest)) {
                $newest = $time;
            }
        }

        return $newest === null ? null : max(0.0, (time() - $newest) / 3600);
    }

    /** @throws BackupFailedException */
    public function run(): BackupResult
    {
        $started = hrtime(true);

        $directory = $this->ensureDirectory();
        $driver = DB::connection()->getDriverName();
        $extension = $driver === 'sqlite' ? 'sqlite.gz' : 'sql.gz';

        $final = $directory.DIRECTORY_SEPARATOR.self::PREFIX.now()->utc()->format('Ymd-His').'.'.$extension;
        $partial = $final.'.part';

        if (is_file($final)) {
            throw new BackupFailedException('Cadangan dengan nama yang sama sudah ada. Tunggu satu detik lalu coba lagi.');
        }

        $handle = @gzopen($partial, 'wb9');
        if ($handle === false) {
            throw new BackupFailedException('Tidak bisa menulis ke folder cadangan.');
        }

        try {
            match ($driver) {
                'mysql', 'mariadb' => $this->dumpMysql($handle),
                'sqlite' => $this->copySqlite($handle),
                default => throw new BackupFailedException("Driver database \"{$driver}\" belum didukung untuk cadangan."),
            };
        } catch (Throwable $e) {
            gzclose($handle);
            @unlink($partial);

            throw $e instanceof BackupFailedException ? $e : new BackupFailedException('Pencadangan gagal.');
        }

        gzclose($handle);

        clearstatcache(true, $partial);
        $bytes = (int) filesize($partial);

        // gzip kosong berisi sekitar 20 byte; di bawah itu berarti tidak ada isi yang tertulis.
        if ($bytes < 64) {
            @unlink($partial);

            throw new BackupFailedException('Cadangan kosong, tidak disimpan.');
        }

        if (! @rename($partial, $final)) {
            @unlink($partial);

            throw new BackupFailedException('Tidak bisa menyelesaikan berkas cadangan.');
        }

        @chmod($final, 0600);

        return new BackupResult(
            fileName: basename($final),
            bytes: $bytes,
            pruned: $this->prune($final),
            durationMs: (int) ((hrtime(true) - $started) / 1_000_000),
        );
    }

    /**
     * @param  resource  $gz
     *
     * @throws BackupFailedException
     */
    private function dumpMysql($gz): void
    {
        $config = DB::connection()->getConfig();
        $database = (string) ($config['database'] ?? '');
        $socket = (string) ($config['unix_socket'] ?? '');

        $command = [
            (string) config('relaxboss.backup.mysqldump_path'),
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
        ];

        foreach (preg_split('/\s+/', trim((string) config('relaxboss.backup.mysqldump_extra')), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $option) {
            $command[] = $option;
        }

        if ($socket !== '') {
            $command[] = '--socket='.$socket;
        } else {
            $command[] = '--host='.(string) ($config['host'] ?? '127.0.0.1');
            $command[] = '--port='.(string) ($config['port'] ?? '3306');
        }

        $command[] = '--user='.(string) ($config['username'] ?? '');
        $command[] = $database;

        $process = new Process($command, null, ['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
        $process->setTimeout((float) config('relaxboss.backup.timeout_seconds'));

        try {
            $process->run(function (string $type, string $buffer) use ($gz): void {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buffer);
                }
            });
        } catch (Throwable) {
            throw new BackupFailedException('mysqldump tidak bisa dijalankan atau melewati batas waktu.');
        }

        if (! $process->isSuccessful()) {
            throw new BackupFailedException(
                'mysqldump gagal (kode '.(int) $process->getExitCode().').',
                mb_substr(trim($process->getErrorOutput()), 0, 300),
            );
        }
    }

    /**
     * @param  resource  $gz
     *
     * @throws BackupFailedException
     */
    private function copySqlite($gz): void
    {
        $path = (string) DB::connection()->getConfig('database');

        if ($path === '' || $path === ':memory:' || ! is_file($path)) {
            throw new BackupFailedException('Berkas database SQLite tidak ditemukan.');
        }

        $source = @fopen($path, 'rb');
        if ($source === false) {
            throw new BackupFailedException('Berkas database SQLite tidak bisa dibaca.');
        }

        while (! feof($source)) {
            $chunk = fread($source, 1_048_576);
            if ($chunk === false) {
                fclose($source);

                throw new BackupFailedException('Gagal membaca berkas database SQLite.');
            }
            gzwrite($gz, $chunk);
        }

        fclose($source);
    }

    /** Hapus cadangan yang lebih tua dari masa retensi; berkas yang baru dibuat tidak pernah disentuh. */
    private function prune(string $keep): int
    {
        $cutoff = now()->subDays((int) config('relaxboss.backup.retention_days'))->getTimestamp();
        $pruned = 0;

        foreach ($this->files() as $file) {
            if ($file === $keep) {
                continue;
            }

            $time = filemtime($file);
            if ($time !== false && $time < $cutoff && @unlink($file)) {
                $pruned++;
            }
        }

        return $pruned;
    }

    /** @return list<string> */
    private function files(): array
    {
        $found = glob($this->directory().DIRECTORY_SEPARATOR.self::PREFIX.'*.gz');

        return $found === false ? [] : array_values($found);
    }

    /** @throws BackupFailedException */
    private function ensureDirectory(): string
    {
        $directory = $this->directory();

        if (! is_dir($directory) && ! @mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new BackupFailedException('Tidak bisa membuat folder cadangan.');
        }

        return $directory;
    }
}
