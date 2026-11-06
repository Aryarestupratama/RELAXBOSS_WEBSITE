<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Chat\CrisisContactList;
use Illuminate\Http\JsonResponse;

/**
 * API-M011. Kontak bantuan untuk disimpan di perangkat (RULE-M034). `phone` dikirim apa adanya;
 * format `tel:` diputuskan di TASK-M025 (OQ-MA9).
 */
final class CrisisContactsController extends Controller
{
    public function __invoke(CrisisContactList $contacts): JsonResponse
    {
        return response()->json(['data' => [
            'version' => $contacts->version(),
            'contacts' => $contacts->all(),
        ]]);
    }
}
