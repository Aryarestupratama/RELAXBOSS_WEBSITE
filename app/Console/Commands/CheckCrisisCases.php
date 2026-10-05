<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Intent;
use App\Services\Chat\CrisisDetector;
use Illuminate\Console\Command;

/**
 * Alat bantu pemeriksaan manual RULE-047: jalankan sebelum push setiap kali prompt AI atau
 * `resources/crisis/keywords.id.php` berubah. Membaca `resources/crisis/cases.json` dan memastikan
 * semua kasus positif memicu Crisis Response (chat dan PFA) serta kasus negatif tidak.
 *
 * Tidak menyentuh database dan tidak menulis log. Dengan argumen teks, hanya memeriksa satu teks.
 */
final class CheckCrisisCases extends Command
{
    private const MIN_CASES = 30;

    protected $signature = 'relaxboss:check-crisis
        {text? : Periksa satu teks saja (tanpa membaca cases.json)}
        {--cases= : Path cases.json (bawaan: resources/crisis/cases.json)}';

    protected $description = 'Periksa kasus uji krisis (RULE-047, M-5)';

    public function handle(CrisisDetector $detector): int
    {
        $text = $this->argument('text');

        if (is_string($text) && $text !== '') {
            $intent = $detector->detect($text);
            $this->line($intent === null ? 'Tidak terdeteksi krisis.' : "Terdeteksi krisis: {$intent->value}");

            return self::SUCCESS;
        }

        $path = (string) ($this->option('cases') ?: resource_path('crisis/cases.json'));

        if (! is_file($path)) {
            $this->error("Berkas kasus tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $cases = json_decode((string) file_get_contents($path), true);

        if (! is_array($cases)) {
            $this->error('cases.json tidak valid.');

            return self::FAILURE;
        }

        $failures = [];
        $positives = 0;
        $negatives = 0;

        foreach ($cases as $index => $case) {
            $number = $index + 1;
            $caseText = (string) ($case['text'] ?? '');
            $expected = (bool) ($case['expected'] ?? false);
            $context = (string) ($case['context'] ?? 'chat');
            $category = (string) ($case['category'] ?? 'none');

            $expected ? $positives++ : $negatives++;

            $intent = $detector->detect($caseText);
            $actual = $intent !== null;

            if ($actual !== $expected) {
                $failures[] = [$number, $context, $expected ? 'harus krisis' : 'bukan krisis', $intent?->value ?? 'tidak terdeteksi', $caseText];

                continue;
            }

            if ($expected && $category !== 'none' && $intent !== $this->intentFor($category)) {
                $failures[] = [$number, $context, "kategori {$category}", $intent?->value ?? '-', $caseText];
            }
        }

        $total = $positives + $negatives;
        $this->line("Kasus: {$total} (positif {$positives}, negatif {$negatives}).");

        if ($total < self::MIN_CASES) {
            $this->error('Kasus kurang dari ' . self::MIN_CASES . ' (RULE-047, M-5).');

            return self::FAILURE;
        }

        if ($failures !== []) {
            $this->table(['#', 'Konteks', 'Seharusnya', 'Hasil', 'Teks'], $failures);
            $this->error(count($failures) . ' kasus gagal. Perbaiki keywords.id.php sebelum push.');

            return self::FAILURE;
        }

        $this->info('Semua kasus lolos.');

        return self::SUCCESS;
    }

    private function intentFor(string $category): ?Intent
    {
        return match ($category) {
            'suicide' => Intent::CrisisSuicide,
            'selfharm' => Intent::CrisisSelfharm,
            default => null,
        };
    }
}
