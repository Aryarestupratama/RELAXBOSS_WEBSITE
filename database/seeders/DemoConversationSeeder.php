<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ConversationStatus;
use App\Enums\MessageRole;
use App\Models\Conversation;
use App\Models\User;
use App\Support\Wib;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Percakapan DUMMY (RULE-008) untuk akun `example.test`. Hanya lokal.
 * Dipakai menguji enkripsi, relasi pemilik, dan isolasi data sebelum UI RelaxMate (TASK-020).
 * Tanpa balasan AI sungguhan dan tanpa percakapan krisis. Kolom intent dibiarkan kosong
 * (enum `Intent` baru ada di TASK-017).
 *
 * Idempotent: percakapan akun demo dihapus lalu dibuat ulang setiap dijalankan.
 */
class DemoConversationSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoConversationSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        $demoA = User::query()->where('email', 'demo-a@example.test')->first();
        $demoB = User::query()->where('email', 'demo-b@example.test')->first();

        if ($demoA === null || $demoB === null) {
            $this->command?->warn('DemoConversationSeeder dilewati: jalankan DemoAccountSeeder lebih dulu.');

            return;
        }

        DB::transaction(function () use ($demoA, $demoB): void {
            $demoA->conversations()->delete();
            $demoB->conversations()->delete();

            $this->conversation($demoA, ConversationStatus::Active, 2, '[DEMO] Aku capek sekali minggu ini.', 1);
            $this->conversation($demoA, ConversationStatus::Archived, 1, '[DEMO] Percakapan lama yang sudah diarsipkan.', 5);
            $this->conversation($demoB, ConversationStatus::Active, 1, '[DEMO B] Percakapan akun B, tidak boleh terlihat oleh akun A.', 2);
        });
    }

    private function conversation(User $user, ConversationStatus $status, int $turns, string $opening, int $daysAgo): void
    {
        $startedAt = Wib::now()->subDays($daysAgo)->setTime(20, 0);

        $conversation = $user->conversations()->create([
            'status' => $status,
            'total_turns' => $turns,
            'has_crisis' => false,
            'last_message_at' => $startedAt->addMinutes($turns * 2)->utc(),
        ]);
        $conversation->forceFill(['created_at' => $startedAt->utc()])->save();

        $number = 1;
        for ($turn = 1; $turn <= $turns; $turn++) {
            $conversation->messages()->create([
                'turn_number' => $number++,
                'role' => MessageRole::User,
                'content' => $turn === 1 ? $opening : '[DEMO] Terima kasih, aku coba dulu.',
            ]);
            $conversation->messages()->create([
                'turn_number' => $number++,
                'role' => MessageRole::Assistant,
                'content' => '[DEMO] Balasan contoh (bukan dari AI).',
            ]);
        }
    }
}
