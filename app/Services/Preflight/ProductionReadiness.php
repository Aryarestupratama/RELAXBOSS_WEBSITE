<?php

declare(strict_types=1);

namespace App\Services\Preflight;

use App\DTOs\PreflightCheck;
use App\Enums\AiFeature;
use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\User;
use App\Services\Ai\PromptLoader;
use App\Services\Article\ArticleClaimScanner;
use App\Services\Backup\DatabaseBackup;
use RuntimeException;
use Throwable;

/**
 * Pengaman produksi (RULE-048, Architecture bagian 12).
 *
 * - `bootChecks()`: hanya membaca config, tanpa database. Dipakai `EnsureProductionSafe` pada setiap
 *   permintaan saat `APP_ENV=production` (gagal = 503) dan oleh `relaxboss:preflight`.
 * - `allChecks()`: semua pemeriksaan, termasuk database dan berkas. Hanya dipakai `relaxboss:preflight`.
 *
 * Tidak ada nilai rahasia (sandi, key, email) yang dikembalikan atau dicatat.
 */
final class ProductionReadiness
{
    /** Nilai contoh dari `.env.example` yang tidak boleh dipakai di produksi. */
    private const PLACEHOLDER_ADMIN_PASSWORDS = ['isi-sandi-lokal-min-8'];

    private const ADMIN_PASSWORD_MIN_LENGTH = 12;

    /** Jumlah artikel yang disebut di petunjuk pemeriksaan klaim; sisanya diringkas. */
    private const CLAIM_HINT_LIMIT = 5;

    public function __construct(
        private readonly PromptLoader $prompts,
        private readonly DatabaseBackup $backup,
        private readonly ArticleClaimScanner $claims,
    ) {}

    /** Aman dilayani? Hanya pemeriksaan config yang blocking. */
    public function isSafeToServe(): bool
    {
        foreach ($this->bootChecks() as $check) {
            if ($check->blocking && ! $check->passed) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<PreflightCheck>
     */
    public function bootChecks(): array
    {
        $mailer = (string) config('mail.default');

        return [
            new PreflightCheck(
                'app_debug',
                'APP_DEBUG nonaktif',
                config('app.debug') !== true,
                'Setel APP_DEBUG=false di .env produksi.',
            ),
            new PreflightCheck(
                'mail_mailer',
                'Email benar-benar terkirim (bukan log atau array)',
                ! in_array($mailer, ['log', 'array'], true),
                'Setel MAIL_MAILER ke smtp (atau penyedia lain). Saat ini email hanya ditulis ke log.',
            ),
            new PreflightCheck(
                'crisis_contacts',
                'Kontak bantuan krisis terverifikasi (bukan kosong atau dummy)',
                $this->crisisContactsAreReal(),
                'Isi crisis_contacts di config/relaxboss.php dengan kontak asli (OQ-3, TASK-030).',
            ),
        ];
    }

    /**
     * @return list<PreflightCheck>
     */
    public function allChecks(): array
    {
        $checks = [
            new PreflightCheck(
                'app_env',
                'APP_ENV=production',
                app()->isProduction(),
                'Setel APP_ENV=production di .env produksi.',
            ),
            ...$this->bootChecks(),
            new PreflightCheck(
                'app_url_https',
                'APP_URL memakai https',
                str_starts_with((string) config('app.url'), 'https://'),
                'Setel APP_URL ke alamat https:// yang sebenarnya.',
            ),
            new PreflightCheck(
                'session_secure',
                'Cookie sesi hanya lewat https (SESSION_SECURE_COOKIE=true)',
                config('session.secure') === true,
                'Setel SESSION_SECURE_COOKIE=true. Tanpa ini cookie sesi boleh dikirim lewat http.',
            ),
            new PreflightCheck(
                'session_flags',
                'Cookie sesi httpOnly dan SameSite lax atau strict',
                config('session.http_only') === true && in_array(config('session.same_site'), ['lax', 'strict'], true),
                'Biarkan SESSION_HTTP_ONLY=true dan SESSION_SAME_SITE=lax.',
            ),
            new PreflightCheck(
                'assets_built',
                'Aset front-end sudah dibangun (public/build ada, public/hot tidak ada)',
                is_file(public_path('build/manifest.json')) && ! is_file(public_path('hot')),
                'Jalankan npm run build di lokal lalu unggah public/build; hapus public/hot.',
            ),
            new PreflightCheck(
                'queue_sync',
                'QUEUE_CONNECTION=sync (tidak ada queue worker, RULE-034)',
                config('queue.default') === 'sync',
                'Setel QUEUE_CONNECTION=sync.',
                blocking: false,
            ),
            new PreflightCheck(
                'log_level',
                'LOG_LEVEL bukan debug',
                $this->logLevel() !== 'debug',
                'Setel LOG_LEVEL=warning agar log produksi ringkas.',
                blocking: false,
            ),
        ];

        $checks[] = $this->backupCheck();

        foreach (AiFeature::cases() as $feature) {
            $checks[] = $this->promptCheck($feature);
            $checks[] = new PreflightCheck(
                "ai_keys_{$feature->value}",
                "API key tersedia untuk fitur {$feature->value}",
                count((array) config("relaxboss.ai.keys.{$feature->value}")) > 0,
                'Isi GROQ_KEYS_'.strtoupper($feature->value).' di .env. Tanpa key, fitur memakai teks statis.',
                blocking: false,
            );
        }

        return [...$checks, ...$this->databaseChecks()];
    }

    private function crisisContactsAreReal(): bool
    {
        $contacts = array_filter((array) config('relaxboss.crisis_contacts'), 'is_array');

        if ($contacts === []) {
            return false;
        }

        $reachable = false;

        foreach ($contacts as $contact) {
            if (($contact['dummy'] ?? false) === true || ! filled($contact['name'] ?? null)) {
                return false;
            }

            $url = $contact['url'] ?? null;
            if (filled($contact['phone'] ?? null) || (is_string($url) && str_starts_with($url, 'https://'))) {
                $reachable = true;
            }
        }

        return $reachable;
    }

    private function logLevel(): string
    {
        $default = (string) config('logging.default');
        $channel = $default === 'stack'
            ? (string) (((array) config('logging.channels.stack.channels'))[0] ?? 'single')
            : $default;

        return strtolower((string) config("logging.channels.{$channel}.level", 'debug'));
    }

    /** Halaman Privasi berjanji ada cadangan harian (RULE-049), jadi cadangan terbaru wajib ada. */
    private function backupCheck(): PreflightCheck
    {
        $maxAge = (float) config('relaxboss.backup.max_age_hours');
        $age = $this->backup->latestAgeHours();

        return new PreflightCheck(
            'backup_recent',
            'Ada cadangan database dalam '.(int) $maxAge.' jam terakhir',
            $age !== null && $age <= $maxAge,
            'Jalankan php artisan relaxboss:backup-db sekali, lalu pastikan cron schedule:run aktif (TASK-031).',
        );
    }

    private function promptCheck(AiFeature $feature): PreflightCheck
    {
        $label = "Prompt AI {$feature->value} sudah diisi (bukan [PLACEHOLDER])";

        try {
            $placeholder = $this->prompts->load($feature)->isPlaceholder;
        } catch (RuntimeException) {
            return new PreflightCheck("prompt_{$feature->value}", $label, false, 'Berkas prompt tidak ditemukan di resources/prompts/.');
        }

        return new PreflightCheck(
            "prompt_{$feature->value}",
            $label,
            ! $placeholder,
            'Ganti isi berkas prompt dengan prompt asli milik founder (RULE-036, TASK-030).',
        );
    }

    /**
     * Petunjuk pemeriksaan klaim artikel (RULE-049). Hanya slug dan jenis temuan, tidak ada isi artikel.
     *
     * @param  array<string, list<string>>  $flagged  slug => jenis temuan
     */
    private function claimHint(array $flagged): string
    {
        $parts = [];

        foreach (array_slice($flagged, 0, self::CLAIM_HINT_LIMIT, true) as $slug => $kinds) {
            $parts[] = $slug.' ('.implode(', ', $kinds).')';
        }

        $rest = count($flagged) - count($parts);

        return 'Baca ulang sebelum go-live (RULE-049), pemindai ini hanya heuristik: '
            .implode('; ', $parts)
            .($rest > 0 ? "; dan {$rest} artikel lain" : '')
            .'. Klaim UMB hanya boleh tampil setelah OQ-2 terkonfirmasi.';
    }

    /**
     * @return list<PreflightCheck>
     */
    private function databaseChecks(): array
    {
        try {
            $demoAssessments = Assessment::query()->active()->where('slug', 'like', 'demo-%')->exists();
            $demoAccounts = User::query()->where('email', 'like', '%@example.test')->exists();
            // Tanpa scope `published()`: scope itu sudah menyembunyikan `demo-` di produksi, sedangkan
            // pemeriksaan ini justru mencari barisnya. Artikel terjadwal (terbit, tanggal belum tiba) ikut dihitung.
            $demoArticles = Article::query()
                ->where('slug', 'like', 'demo-%')
                ->where('status', ArticleStatus::Published->value)
                ->exists();
            $flaggedArticles = $this->claims->flaggedPublished();
            $realAdmin = User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->whereNotNull('email_verified_at')
                ->where('email', 'not like', '%@example.test')
                ->exists();
        } catch (Throwable) {
            return [new PreflightCheck('database', 'Database dapat dibaca', false, 'Periksa DB_* di .env dan jalankan php artisan migrate --force.')];
        }

        $seedPassword = config('relaxboss.admin_seed.password');
        $seedPasswordOk = ! filled($seedPassword)
            || (is_string($seedPassword)
                && ! in_array($seedPassword, self::PLACEHOLDER_ADMIN_PASSWORDS, true)
                && strlen($seedPassword) >= self::ADMIN_PASSWORD_MIN_LENGTH);

        return [
            new PreflightCheck('database', 'Database dapat dibaca', true, ''),
            new PreflightCheck(
                'demo_assessments',
                'Tidak ada Instrumen demo- yang aktif',
                ! $demoAssessments,
                'Nonaktifkan atau hapus Instrumen berslug demo- (data nyata di TASK-030).',
            ),
            new PreflightCheck(
                'demo_accounts',
                'Tidak ada akun @example.test',
                ! $demoAccounts,
                'Hapus akun dummy (demo-a, demo-b, dan lainnya) dari database produksi.',
            ),
            new PreflightCheck(
                'demo_articles',
                'Tidak ada artikel demo- yang berstatus terbit',
                ! $demoArticles,
                'Kembalikan artikel berslug demo- ke draf lewat Admin Artikel (tidak ada tombol hapus), lalu terbitkan artikel asli.',
            ),
            new PreflightCheck(
                'article_claims',
                'Artikel terbit tidak memuat klaim yang belum terkonfirmasi (UMB, validasi, diagnosis)',
                $flaggedArticles === [],
                $this->claimHint($flaggedArticles),
                blocking: false,
            ),
            new PreflightCheck(
                'admin_exists',
                'Ada admin aktif dan terverifikasi dengan email asli',
                $realAdmin,
                'Isi ADMIN_SEED_EMAIL dan ADMIN_SEED_PASSWORD di .env lalu jalankan AdminSeeder.',
            ),
            new PreflightCheck(
                'admin_seed_secret',
                'ADMIN_SEED_PASSWORD bukan contoh dan minimal '.self::ADMIN_PASSWORD_MIN_LENGTH.' karakter (atau sudah dihapus dari .env)',
                $seedPasswordOk,
                'Pakai sandi kuat dan unik; hapus baris ADMIN_SEED_PASSWORD setelah admin dibuat.',
            ),
        ];
    }
}
