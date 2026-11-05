<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\IssueMobileToken;
use App\Actions\Auth\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\AuthSessionResource;
use Illuminate\Http\JsonResponse;

/** API-M001. Aturan sama dengan web (FR-001), termasuk `terms` wajib `true`. */
final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUser $registerUser, IssueMobileToken $issueToken): JsonResponse
    {
        /** @var array{name: string, email: string, password: string, institution_name?: string|null, major?: string|null} $data */
        $data = $request->safe()->except('terms');

        $result = $registerUser->handle($data);

        return (new AuthSessionResource([
            'token' => $issueToken->handle($result['user']),
            'user' => $result['user'],
            'email_sent' => $result['email_sent'],
        ]))->response()->setStatusCode(201);
    }
}
