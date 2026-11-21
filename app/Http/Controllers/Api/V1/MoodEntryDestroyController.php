<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * API-M022: hapus Mood Entry milik sendiri (permanen). Entri milik orang lain atau tidak ada: 404 (RULE-033).
 * Menghapus entri mengurangi hitungan batas harian dan menggeser "catatan terakhir" di grafik.
 */
final class MoodEntryDestroyController extends Controller
{
    public function __invoke(Request $request, string $entry): Response
    {
        $request->user()->moodEntries()->findOrFail($entry)->delete();

        return response()->noContent();
    }
}
