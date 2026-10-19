<?php

use Illuminate\Support\Facades\Schedule;

// Dijalankan cron `* * * * * php artisan schedule:run` (Architecture bagian 12, RULE-034).
// Tanpa queue worker atau proses permanen.

// Cadangan database harian, retensi dari `relaxboss.backup.retention_days`.
Schedule::command('relaxboss:backup-db')
    ->dailyAt('02:00')
    ->timezone((string) config('relaxboss.timezone'))
    ->withoutOverlapping();

// Hapus token reset sandi yang sudah kedaluwarsa.
Schedule::command('auth:clear-resets')
    ->dailyAt('03:00')
    ->timezone((string) config('relaxboss.timezone'))
    ->withoutOverlapping();
