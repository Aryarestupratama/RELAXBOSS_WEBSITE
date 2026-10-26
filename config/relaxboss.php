<?php

declare(strict_types=1);

// Kontak Laboratorium Terapan Psikologi (OQ-2, OQ-3): hanya tampil bila PSYCH_LAB_HOTLINE_PHONE terisi
// di .env, yaitu setelah dikonfirmasi tertulis (RULE-049). Nomor dinormalkan ke format +62.
$labDigits = preg_replace('/\D+/', '', (string) env('PSYCH_LAB_HOTLINE_PHONE', '')) ?? '';
if (str_starts_with($labDigits, '0')) {
    $labDigits = '62'.substr($labDigits, 1);
}

return [
    'admin_seed' => [
        'email' => env('ADMIN_SEED_EMAIL'),
        'password' => env('ADMIN_SEED_PASSWORD'),
    ],

    'timezone' => 'Asia/Jakarta',
    'ai_consent_version' => 1,
    'ai_training_consent_version' => 1,

    'limits' => [
        'chat_per_day' => 20,
        'chat_per_minute' => 5,
        'message_max_chars' => 2000,
        'context_max_messages' => 10,
        'context_max_chars' => 9000,
        'mood_per_day' => 5,
        'mood_note_max_chars' => 500,
        'mood_per_minute' => 10,
        'assessment_ai_per_day' => 5,
        'pfa_answer_max_chars' => 1000,
        'history_per_page' => 15,
        // Artikel edukasi per halaman di /artikel (FR-021). Angka asumsi (RULE-026).
        'articles_per_page' => 9,
        // Artikel terkait di bawah artikel (tautan internal). Angka asumsi.
        'articles_related' => 3,
    ],

    'admin' => [
        // Kelompok di bawah angka ini disamarkan di semua statistik admin (RULE-042).
        'min_group_size' => 5,

        // Jumlah akun per halaman di daftar Pengguna admin (FR-024). Angka asumsi (RULE-026).
        'users_per_page' => 25,

        // Admin Artikel (FR-021, SCR-026). Angka asumsi (RULE-026).
        'articles' => [
            'per_page' => 25,
            // Batas panjang isi Markdown (karakter).
            'body_max_chars' => 100000,
        ],

        // Tinjauan admin tanpa identitas (FR-031, SCR-022). Angka asumsi (RULE-026).
        'monitoring' => [
            'per_page' => 20,
            // Jumlah Percakapan pada "Sampel acak".
            'sample_size' => 10,
            // Balasan AI dengan keyakinan di bawah angka ini ditandai "Keyakinan rendah" (0 sampai 1).
            'low_confidence' => 0.5,
        ],

        // Batas formulir Instrumen (FR-023). Angka asumsi; ubah di sini, bukan di kode (RULE-026).
        'assessment' => [
            'max_questions' => 100,
            'max_options' => 10,
            'max_rules' => 60,
            'max_option_value' => 100,
        ],
    ],

    'auth' => [
        'password_min_length' => 8,
        'verification_expire_minutes' => 60,
        'register_per_minute' => 5,
        'resend_per_hour' => 3,
        'resend_window_seconds' => 3600,
        'login_per_minute' => 5,
        'login_window_seconds' => 60,
        'delete_account_per_minute' => 5,
        'change_password_per_minute' => 5,
        'reset_expire_minutes' => 60,
    ],

    // Foto profil (FR-033). Berkas ada di disk privat dan hanya disajikan kepada pemiliknya (RULE-042).
    'avatar' => [
        'max_kb' => 2048,
        'max_pixels' => 4096,
        'size' => 256,
        'quality' => 85,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'per_minute' => 10,
    ],

    'ai' => [
        // Pool API key per fitur (ADR-009): daftar dipisah koma di .env. Jangan pernah dicatat di log (RULE-040, RULE-043).
        'keys' => [
            'chat' => array_values(array_filter(array_map('trim', explode(',', (string) env('GROQ_KEYS_CHAT', ''))))),
            'assessment' => array_values(array_filter(array_map('trim', explode(',', (string) env('GROQ_KEYS_ASSESSMENT', ''))))),
        ],

        'features' => [
            'chat' => [
                'prompt' => 'relaxmate.system.md',
                'model' => env('GROQ_MODEL_PRIMARY', 'openai/gpt-oss-120b'),
                'fallback_model' => env('GROQ_MODEL_FALLBACK', 'openai/gpt-oss-20b'),
                'max_completion_tokens' => (int) env('AI_MAX_OUTPUT_TOKENS', 800),
                'reasoning_effort' => 'low',
                'timeout' => (int) env('GROQ_TIMEOUT_SECONDS', 20),
            ],
            'assessment' => [
                'prompt' => 'assessment-recommendation.system.md',
                'model' => env('GROQ_MODEL_PRIMARY', 'openai/gpt-oss-120b'),
                'fallback_model' => env('GROQ_MODEL_FALLBACK', 'openai/gpt-oss-20b'),
                'max_completion_tokens' => (int) env('AI_MAX_OUTPUT_TOKENS', 800),
                'reasoning_effort' => 'low',
                'timeout' => (int) env('GROQ_TIMEOUT_SECONDS', 20),
            ],
        ],
    ],

    // Cadangan database (TASK-031). Retensi 14 hari juga ditulis di halaman Privasi, jadi angkanya satu tempat ini (RULE-026).
    'backup' => [
        'retention_days' => 14,
        // Relatif terhadap storage_path(); non-publik dan tidak masuk repo (RULE-013).
        'path' => 'app/private/backups',
        // Preflight gagal bila cadangan terbaru lebih tua dari ini (cron harian + selisih).
        'max_age_hours' => 26,
        'timeout_seconds' => 300,
        'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH', 'mysqldump'),
        // Opsi tambahan, dipisah spasi. Hapus nilainya bila mysqldump di hosting tidak mengenal opsi ini.
        'mysqldump_extra' => env('BACKUP_MYSQLDUMP_EXTRA', '--no-tablespaces'),
    ],

    // Kontak bantuan krisis (OQ-3, RULE-049). Hanya yang terverifikasi dari sumber resmi.
    // Healing119.id: layanan Kemenkes, 24 jam, gratis, dilayani tenaga kesehatan terlatih
    // (FAQ Healing119.id, kesprimkom.kemkes.go.id). Verifikasi dengan menelepon sebelum go-live.
    // Tambahkan kontak Laboratorium Terapan Psikologi di sini hanya setelah dikonfirmasi tertulis (OQ-2).
    'crisis_contacts' => [
        [
            'name' => 'Healing119.id (Kementerian Kesehatan)',
            'phone' => '119 ext 8',
            'url' => 'https://www.healing119.id',
            'note' => 'Layanan konseling gratis, 24 jam.',
            'dummy' => false,
        ],
        ...($labDigits === '' ? [] : [[
            'name' => (string) env('PSYCH_LAB_HOTLINE_NAME', 'Laboratorium Terapan Psikologi'),
            'phone' => '+'.$labDigits,
            'url' => null,
            'note' => 'Kontak konsultasi psikologi.',
            'dummy' => false,
        ]]),
    ],

    // Kosong sampai OQ-2 terjawab (RULE-049).
    'umb' => '',

    // Klaim validasi Asesmen di halaman Publik /asesmen (RULE-049). Isi hanya yang sudah terkonfirmasi;
    // kosongkan array untuk menyembunyikan seluruh bagian.
    'assessment_validation' => [
        [
            'icon' => 'graduation-cap',
            'title' => 'Dibantu psikologi Universitas Mercu Buana',
            'text' => 'Asesmen divalidasi dengan bantuan pihak psikologi Universitas Mercu Buana.',
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Divalidasi psikolog berlisensi',
            'text' => 'Asesmen juga divalidasi oleh psikolog berlisensi.',
        ],
    ],

    // Cerita di halaman Tentang (SCR-004). Klaim publik (RULE-049): isi hanya yang sudah terkonfirmasi.
    // Kosongkan sebuah bagian (atau seluruh blok) untuk menyembunyikannya. Jabatan tanpa nama pribadi;
    // tambahkan nama hanya dengan izin yang bersangkutan.
    'about' => [
        'collaboration' => 'RelaxBoss adalah hasil kolaborasi Fakultas Psikologi dan Fakultas Ilmu Komputer Universitas Mercu Buana.',
        'award' => [
            'title' => 'Medali Emas',
            'event' => 'Pekan Inovasi Universitas Mercu Buana',
            'year' => '2025',
        ],
        'supervisors' => [
            'Dekan Fakultas Ilmu Komputer',
            'Dekan Fakultas Psikologi',
            'Kepala Laboratorium Psikologi Terapan',
        ],
    ],

    // Eksportir data AI (FR-030, RULE-072). Fitur AI baru = satu kelas AiDataExporter + satu baris di sini.
    'exporters' => [
        \App\Services\Export\ChatExporter::class,
        \App\Services\Export\AssessmentExporter::class,
    ],
];