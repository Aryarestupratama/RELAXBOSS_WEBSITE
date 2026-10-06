<?php

declare(strict_types=1);

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
    ],

    'admin' => [
        // Kelompok di bawah angka ini disamarkan di semua statistik admin (RULE-042).
        'min_group_size' => 5,

        // Jumlah akun per halaman di daftar Pengguna admin (FR-024). Angka asumsi (RULE-026).
        'users_per_page' => 25,

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
        'reset_expire_minutes' => 60,
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

    // Dummy sampai TASK-030 (RULE-008, RULE-048).
    'crisis_contacts' => [
        ['name' => 'Kontak Krisis Dummy', 'phone' => null, 'url' => null, 'note' => 'dummy', 'dummy' => true],
    ],

    // Kosong sampai OQ-2 terjawab (RULE-049).
    'umb' => '',

    // Eksportir data AI (FR-030, RULE-072). Fitur AI baru = satu kelas AiDataExporter + satu baris di sini.
    'exporters' => [
        \App\Services\Export\ChatExporter::class,
        \App\Services\Export\AssessmentExporter::class,
    ],
];