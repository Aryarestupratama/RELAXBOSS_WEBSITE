<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API mobile `/api/v1` (Architecture-M bagian 5)
|--------------------------------------------------------------------------
| Stateless, token Sanctum (ADR-M02). Controller ada di app/Http/Controllers/Api/V1,
| tipis, dengan FormRequest dan API Resource. Prefix `/api` dari bootstrap/app.php.
| Endpoint ditambahkan pada task fiturnya: auth (TASK-M013), ringkasan (TASK-M014),
| chat (TASK-M015), hapus akun (TASK-M016).
*/
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    //
});
