<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\NewAccessToken;

/**
 * Sesi hasil register atau login (API-M001, API-M002). Waktu dalam ISO-8601 UTC (RULE-M033).
 */
final class AuthSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{token: NewAccessToken, user: User, email_sent?: bool} $session */
        $session = $this->resource;
        $expiresAt = $session['token']->accessToken->expires_at;

        return [
            'token' => $session['token']->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt?->toImmutable()->utc()->toIso8601ZuluString(),
            'user' => new UserSummaryResource($session['user']),
            ...(array_key_exists('email_sent', $session) ? ['email_sent' => $session['email_sent']] : []),
        ];
    }
}
