<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Consent\GrantAiConsent;
use App\Http\Controllers\Controller;
use App\Services\Consent\ConsentInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** API-M009. Catat consent AI versi terbaru (Action yang sama dengan web, idempoten). */
final class AiConsentController extends Controller
{
    public function __invoke(Request $request, GrantAiConsent $action, ConsentInfo $consent): JsonResponse
    {
        $user = $request->user();

        $action->handle($user);

        return response()->json(['data' => ['consent' => $consent->state($user)]]);
    }
}
