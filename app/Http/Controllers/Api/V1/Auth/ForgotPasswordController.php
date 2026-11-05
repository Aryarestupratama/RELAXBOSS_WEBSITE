<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;

/**
 * API-M004. Respons selalu sama (email terdaftar atau tidak) agar email tidak bisa ditebak.
 * Penggantian sandi diselesaikan di halaman web `/reset-sandi/{token}`.
 */
final class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, SendPasswordResetLink $sendLink): JsonResponse
    {
        $sendLink->handle((string) $request->validated('email'));

        return response()->json(['data' => [
            'message' => 'Jika email itu terdaftar, tautan untuk mengatur ulang kata sandi sudah dikirim. Cek kotak masukmu, ya.',
        ]]);
    }
}
