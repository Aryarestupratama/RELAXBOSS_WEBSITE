<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Account\DeleteUserAccount;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\DeleteAccountRequest;
use Illuminate\Http\Response;

/**
 * API-M017 (FR-M003, API-016 web): hapus akun dan seluruh data setelah konfirmasi kata sandi.
 * Aturan, batas percobaan, dan penolakan admin terakhir ada di Action yang sama dengan web; galatnya
 * (`password`, `account`) keluar sebagai 422 `validation_failed` dengan `errors` per kolom.
 * Semua token pengguna dihapus di dalam Action. Balasan 204 tanpa isi.
 */
final class DeleteAccountController extends Controller
{
    public function __invoke(DeleteAccountRequest $request, DeleteUserAccount $deleteAccount): Response
    {
        $deleteAccount->handle($request->user(), (string) $request->validated('password'));

        return response()->noContent();
    }
}
