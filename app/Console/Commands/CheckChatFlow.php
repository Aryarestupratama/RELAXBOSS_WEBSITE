<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Chat\ChatFlowChecker;
use Illuminate\Console\Command;

/**
 * Alat bantu pemeriksaan manual: jalankan setiap kali `ChatReplyParser`, `ConversationPlanner`, atau prompt
 * RelaxMate berubah. Memeriksa pembacaan keluaran model (termasuk sisa metadata yang bocor) dan pemilihan
 * jenis balasan per giliran. Tidak memanggil AI, tidak menyentuh database, tidak menulis log.
 */
final class CheckChatFlow extends Command
{
    protected $signature = 'relaxboss:check-chat';

    protected $description = 'Periksa parser balasan chat dan perencana giliran (tanpa AI dan database)';

    public function handle(ChatFlowChecker $checker): int
    {
        $result = $checker->run();

        foreach ($result['failures'] as $failure) {
            $this->error($failure);
        }

        if ($result['failures'] !== []) {
            $this->line(count($result['failures'])." dari {$result['total']} kasus gagal.");

            return self::FAILURE;
        }

        $this->info("Semua {$result['total']} kasus lolos.");

        return self::SUCCESS;
    }
}
