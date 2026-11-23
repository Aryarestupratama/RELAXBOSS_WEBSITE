<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Chat\CrisisContactList;
use Illuminate\Http\JsonResponse;

/**
 * API-M007. Publik (tanpa token): dibaca aplikasi saat dibuka. Hanya konfigurasi, tanpa data pengguna.
 * Klien memeriksa `min_app_version` dengan gagal-terbuka (ADR-M13). `limits.mood_note_max_chars` memberi batas panjang
 * catatan buku harian ke klien; server tetap yang memvalidasi.
 */
final class ConfigController extends Controller
{
    public function __invoke(CrisisContactList $contacts): JsonResponse
    {
        return response()->json(['data' => [
            'min_app_version' => (string) config('relaxboss.mobile.min_app_version'),
            'crisis_contacts_version' => $contacts->version(),
            'features' => [
                'overlay' => (bool) config('relaxboss.mobile.features.overlay'),
            ],
            // Batas yang ikut menentukan isian di klien (TASK-055): klien tidak menggandakan angkanya (RULE-M026).
            'limits' => [
                'mood_note_max_chars' => (int) config('relaxboss.limits.mood_note_max_chars'),
            ],
        ]]);
    }
}
