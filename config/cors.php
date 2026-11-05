<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CORS dimatikan
|--------------------------------------------------------------------------
| Satu-satunya klien API adalah aplikasi native, yang tidak memakai CORS. Tanpa `paths`,
| header `Access-Control-Allow-Origin` tidak ditambahkan sehingga peramban pihak lain
| tidak diberi izin membaca respons API (RULE-044, Architecture-M bagian 7).
*/
return [
    'paths' => [],
    'allowed_methods' => ['*'],
    'allowed_origins' => [],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
