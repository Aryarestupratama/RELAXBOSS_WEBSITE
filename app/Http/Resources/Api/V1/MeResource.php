<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use App\Services\Chat\CrisisContactList;
use App\Services\Consent\ConsentInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API-M006. Ringkasan akun pemilik token: tanpa ID, peran, atau kolom internal.
 * Batas dibaca dari config (RULE-026) agar klien tidak menggandakan angkanya (RULE-M026).
 */
final class MeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'user' => new UserSummaryResource($user),
            'consent' => app(ConsentInfo::class)->state($user),
            'limits' => [
                'message_max_chars' => (int) config('relaxboss.limits.message_max_chars'),
                'chat_per_day' => (int) config('relaxboss.limits.chat_per_day'),
                'chat_per_minute' => (int) config('relaxboss.limits.chat_per_minute'),
            ],
            'crisis_contacts_version' => app(CrisisContactList::class)->version(),
        ];
    }
}
