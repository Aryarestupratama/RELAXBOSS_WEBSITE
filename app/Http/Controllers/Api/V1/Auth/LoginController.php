<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\IssueMobileToken;
use App\Actions\Auth\LoginUser;
use App\Exceptions\LoginFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\V1\AuthSessionResource;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;

/** API-M002. Tanpa sesi: hanya token Sanctum. Pesan galat umum agar akun tidak bisa ditebak. */
final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUser $login, IssueMobileToken $issueToken): JsonResponse
    {
        try {
            $user = $login->verify(
                (string) $request->validated('email'),
                (string) $request->validated('password'),
                (string) $request->ip(),
            );
        } catch (LoginFailedException $e) {
            return ApiError::response(
                $e->errorCode,
                $e->getMessage(),
                $e->status,
                $e->retryAfter === null ? [] : ['Retry-After' => $e->retryAfter],
            );
        }

        return (new AuthSessionResource([
            'token' => $issueToken->handle($user),
            'user' => $user,
        ]))->response();
    }
}
