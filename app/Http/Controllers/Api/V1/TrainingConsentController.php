<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Consent\RecordTrainingConsent;
use App\Enums\TrainingConsentChoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTrainingConsentRequest;
use App\Services\Consent\ConsentInfo;
use Illuminate\Http\JsonResponse;

/** API-M010. Catat pilihan persetujuan pelatihan; boleh diubah kapan saja (RULE-071). */
final class TrainingConsentController extends Controller
{
    public function __invoke(
        StoreTrainingConsentRequest $request,
        RecordTrainingConsent $action,
        ConsentInfo $consent,
    ): JsonResponse {
        $user = $request->user();

        $action->handle($user, TrainingConsentChoice::from((string) $request->validated('choice')));

        return response()->json(['data' => ['consent' => $consent->state($user)]]);
    }
}
