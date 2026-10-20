<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Artikel contoh DUMMY (RULE-008): slug berawalan `demo-`, isi bukan konten asli.
 * Tiga artikel untuk menguji: terbit, draf, dan terbit dengan tanggal di masa depan. Hanya lokal.
 */
class DemoArticleSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoArticleSeeder dilewati: hanya untuk lingkungan local.');

            return;
        }

        $body = "Ini adalah artikel contoh untuk pengembangan. Isinya bukan konten asli.\n\n"
            ."## Subjudul contoh\n\nParagraf contoh dengan **teks tebal** dan *teks miring*.\n\n"
            ."- Butir pertama\n- Butir kedua\n\n"
            .'<script>alert("html mentah harus dibuang")</script>';

        $rows = [
            [
                'slug' => 'demo-artikel-terbit',
                'title' => '[DEMO] Artikel Contoh Terbit',
                'excerpt' => 'Ringkasan artikel contoh yang sudah terbit.',
                'category' => ArticleCategory::Stress,
                'related_assessment_slug' => 'demo-stres-harian',
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(3),
            ],
            [
                'slug' => 'demo-artikel-draf',
                'title' => '[DEMO] Artikel Contoh Draf',
                'excerpt' => 'Ringkasan artikel contoh yang masih draf.',
                'category' => ArticleCategory::Sleep,
                'related_assessment_slug' => null,
                'status' => ArticleStatus::Draft,
                'published_at' => null,
            ],
            [
                'slug' => 'demo-artikel-terjadwal',
                'title' => '[DEMO] Artikel Contoh Terjadwal',
                'excerpt' => 'Berstatus terbit tetapi tanggal terbitnya belum tiba.',
                'category' => ArticleCategory::Burnout,
                'related_assessment_slug' => null,
                'status' => ArticleStatus::Published,
                'published_at' => now()->addDays(7),
            ],
        ];

        foreach ($rows as $row) {
            Article::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row + [
                    'meta_title' => null,
                    'meta_description' => null,
                    'body_markdown' => $body,
                    'author_name' => 'Tim RelaxBoss (contoh)',
                ],
            );
        }
    }
}
