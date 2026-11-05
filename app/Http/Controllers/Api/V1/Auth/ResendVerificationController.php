<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResendVerificationEmail;
use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** API-M005. Maks. resend_per_hour kali per jam (batas ada di Action, sama dengan web). */
final class ResendVerificationController extends Controller
{
    public function __invoke(Request $request, ResendVerificationEmail $resend): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['data' => ['email_verified' => true, 'email_sent' => false]]);
        }

        try {
            $sent = $resend->handle($user);
        } catch (ValidationException $e) {
            return ApiError::response(
                'rate_limited',
                (string) ($e->errors()['resend'][0] ?? 'Kamu sudah meminta terlalu banyak email. Coba lagi nanti.'),
                429,
            );
        }

        return response()->json(['data' => ['email_verified' => false, 'email_sent' => $sent]]);
    }
}
