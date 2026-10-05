<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | RelaxBoss tidak memakai SSR Node (Architecture, Prinsip 3 dan ADR-002).
    | Halaman Publik memakai Blade untuk SEO, jadi SSR dimatikan.
    |
    */

    'ssr' => [

        'enabled' => (bool) env('INERTIA_SSR_ENABLED', false),

        'runtime' => env('INERTIA_SSR_RUNTIME', 'node'),

        'ensure_runtime_exists' => (bool) env('INERTIA_SSR_ENSURE_RUNTIME_EXISTS', false),

        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        'hot_url' => env('INERTIA_SSR_HOT_URL'),

        'timeout' => env('INERTIA_SSR_TIMEOUT'),

        'ensure_bundle_exists' => (bool) env('INERTIA_SSR_ENSURE_BUNDLE_EXISTS', false),

        // 'bundle' => base_path('bootstrap/ssr/ssr.mjs'),

        'throw_on_error' => (bool) env('INERTIA_SSR_THROW_ON_ERROR', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Path huruf besar `Pages` disengaja (Architecture bagian 11): default
    | Inertia 3 memakai huruf kecil, dan bedanya terasa di Linux/hosting.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [resource_path('js/Pages')],

        'extensions' => [

            'ts',
            'tsx',

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | Proyek ini tanpa test otomatis (Rules.md), jadi pemeriksaan ini dimatikan.
    |
    */

    'testing' => [

        'ensure_pages_exist' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | Expose Shared Prop Keys
    |--------------------------------------------------------------------------
    */

    'expose_shared_prop_keys' => true,

    /*
    |--------------------------------------------------------------------------
    | Previous URL
    |--------------------------------------------------------------------------
    */

    'store_previous_url' => false,

    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    |
    | Pertimbangkan mengaktifkan `encrypt` (INERTIA_ENCRYPT_HISTORY=true) saat
    | halaman berisi data sensitif sudah ada (chat, mood, hasil Asesmen), agar
    | data tidak terbaca dari riwayat browser setelah logout. Dicatat untuk
    | ditinjau di TASK-029.
    |
    */

    'history' => [

        'encrypt' => (bool) env('INERTIA_ENCRYPT_HISTORY', false),

    ],

    /*
    |--------------------------------------------------------------------------
    | DevTools
    |--------------------------------------------------------------------------
    |
    | Hanya untuk lokal. Dibiarkan mati (null) kecuali dibutuhkan.
    |
    */

    'devtools' => [

        'enabled' => env('INERTIA_DEVTOOLS_ENABLED'),

        'except' => ['telescope*', 'horizon*', '_inertia/devtools*'],

        'storage' => [

            'path' => storage_path('inertia-devtools'),

            'ttl' => (int) env('INERTIA_DEVTOOLS_TTL_HOURS', 24),

            'prune_interval' => (int) env('INERTIA_DEVTOOLS_PRUNE_INTERVAL_SECONDS', 300),

            'limit' => (int) env('INERTIA_DEVTOOLS_LIMIT', 100),

        ],

        'middleware' => ['web'],

        'gate' => env('INERTIA_DEVTOOLS_GATE'),

        'redact' => [

            'keys' => [
                'password',
                'password_confirmation',
                'current_password',
                'token',
                '_token',
                'access_token',
                'refresh_token',
                'secret',
                'client_secret',
                'api_key',
            ],

            'headers' => [
                'cookie',
                'set-cookie',
                'authorization',
                'proxy-authorization',
                'x-xsrf-token',
                'x-csrf-token',
            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Big Integers
    |--------------------------------------------------------------------------
    */

    'preserve_big_integers' => (bool) env('INERTIA_PRESERVE_BIG_INTEGERS', false),

];