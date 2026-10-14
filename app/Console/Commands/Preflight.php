<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Preflight\ProductionReadiness;
use Illuminate\Console\Command;

/**
 * `php artisan relaxboss:preflight` (RULE-048, TASK-029). Dijalankan founder sebelum go-live dan setiap deploy.
 * Keluar dengan kode 1 bila ada pemeriksaan blocking yang gagal. Di lokal sengaja gagal (data dummy).
 * Tidak mencetak nilai rahasia.
 */
final class Preflight extends Command
{
    protected $signature = 'relaxboss:preflight';

    protected $description = 'Periksa kesiapan produksi (RULE-048). Gagal = jangan go-live.';

    public function handle(ProductionReadiness $readiness): int
    {
        $rows = [];
        $blocked = 0;
        $warned = 0;

        foreach ($readiness->allChecks() as $check) {
            if ($check->passed) {
                $status = 'OK';
            } elseif ($check->blocking) {
                $status = 'GAGAL';
                $blocked++;
            } else {
                $status = 'PERHATIAN';
                $warned++;
            }

            $rows[] = [$status, $check->label, $check->passed ? '' : $check->hint];
        }

        $this->table(['Status', 'Pemeriksaan', 'Yang perlu dilakukan'], $rows);

        if ($blocked > 0) {
            $this->error("{$blocked} pemeriksaan GAGAL. Jangan go-live.");

            return self::FAILURE;
        }

        $this->info($warned > 0 ? "Semua pemeriksaan wajib lolos ({$warned} peringatan)." : 'Semua pemeriksaan lolos.');

        return self::SUCCESS;
    }
}
