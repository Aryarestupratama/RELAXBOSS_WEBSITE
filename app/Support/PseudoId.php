<?php

declare(strict_types=1);

namespace App\Support;

/**
 * ID semu untuk tinjauan admin (Architecture bagian 8): HMAC(id, APP_KEY) dipotong 8 karakter.
 * Stabil untuk sumber daya yang sama, tidak bisa dibalik ke pemilik, dan tidak memuat ID pengguna.
 */
final class PseudoId
{
    public const LENGTH = 8;

    public static function for(string $resourceType, string|int $resourceId): string
    {
        return substr(hash_hmac('sha256', $resourceType.':'.$resourceId, (string) config('app.key')), 0, self::LENGTH);
    }
}
