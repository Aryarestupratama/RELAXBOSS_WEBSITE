<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Maintenance\ContentPurger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * `php artisan relaxboss:purge-content` (TASK-031). Mengosongkan konten dan data aktivitas dummy
 * sebelum go-live; akun tidak disentuh. Tanpa `--force`, hanya menampilkan jumlah (dry run) lalu
 * meminta konfirmasi. Log hanya memuat jumlah baris (RULE-040).
 */
final class PurgeContent extends Command
{
    protected $signature = 'relaxboss:purge-content
        {--force : Lewati konfirmasi dan langsung hapus}';

    protected $description = 'Hapus Instrumen, hasil Asesmen, artikel, chat, mood, dan log (akun tetap ada). Jalankan relaxboss:backup-db dulu.';

    public function handle(ContentPurger $purger): int
    {
        $counts = $purger->counts();

        $this->table(
            ['Tabel', 'Baris yang akan dihapus'],
            array_map(fn (string $table, int $rows): array => [$table, $rows], array_keys($counts), $counts),
        );
        $this->line('Tabel users (akun), foto profil, dan sesi tidak disentuh.');

        if (array_sum($counts) === 0) {
            $this->info('Tidak ada yang perlu dihapus.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Hapus semua baris di atas secara permanen?', false)) {
            $this->warn('Dibatalkan. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        $deleted = $purger->purge();

        Log::info('content.purged', ['rows' => array_sum($deleted)]);

        $this->info('Selesai. Baris dihapus: '.array_sum($deleted).'.');
        $this->line('Instrumen asli dimasukkan lewat Admin Asesmen, artikel asli lewat Admin Artikel.');

        return self::SUCCESS;
    }
}
