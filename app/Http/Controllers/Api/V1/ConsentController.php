<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Consent\ConsentInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** API-M008. Teks dan versi consent terbaru dari config (sama dengan web), plus status pengguna. */
final class ConsentController extends Controller
{
    public function __invoke(Request $request, ConsentInfo $consent): JsonResponse
    {
        $texts = $consent->texts();
        $versions = $consent->requiredVersions();

        return response()->json(['data' => [
            'ai' => [
                'title' => $texts['ai_title'],
                'text' => $texts['ai_text'],
                'version' => $versions['ai'],
            ],
            'training' => [
                'question' => $texts['training_question'],
                'text' => $texts['training_text'],
                'version' => $versions['training'],
            ],
            'state' => $consent->state($request->user()),
        ]]);
    }
}
