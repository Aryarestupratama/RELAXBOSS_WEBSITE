<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exceptions\BackupFailedException;
use App\Services\Backup\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * `php artisan relaxboss:backup-db` (TASK-031). Dijadwalkan harian di `routes/console.php`
 * dan boleh dijalankan manual. Log hanya memuat ukuran, durasi, dan jumlah yang dihapus (RULE-040).
 */
final class BackupDatabase extends Command
{
    protected $signature = 'relaxboss:backup-db';

    protected $description = 'Cadangkan database ke storage/app/private/backups dan hapus cadangan yang melewati masa retensi.';

    public function handle(DatabaseBackup $backup): int
    {
        try {
            $result = $backup->run();
        } catch (BackupFailedException $e) {
            Log::error('backup.failed', ['code' => 'backup_failed']);

            $this->error($e->getMessage());
            if ($e->detail !== '') {
                $this->line($e->detail);
            }

            return self::FAILURE;
        }

        Log::info('backup.done', [
            'bytes' => $result->bytes,
            'duration_ms' => $result->durationMs,
            'pruned' => $result->pruned,
        ]);

        $this->info("Cadangan dibuat: {$result->fileName} (".number_format($result->bytes / 1024, 1).' KB, '.$result->durationMs.' ms).');
        $this->line('Cadangan lama dihapus: '.$result->pruned.'. Salin APP_KEY secara terpisah dari cadangan.');

        return self::SUCCESS;
    }
}
