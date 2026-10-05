<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// SEMENTARA (TASK-002): halaman uji tampilan. Hapus di TASK-007.
Route::get('/', fn () => Inertia::render('Welcome'));

if (app()->environment('local')) {
    Route::get('/uji/tamu', fn () => Inertia::render('Auth/UjiTamu'));
    Route::get('/uji/error/{code}', fn (int $code) => abort($code))
        ->whereIn('code', [403, 419, 429, 500, 503]);
}

require __DIR__.'/public.php';
