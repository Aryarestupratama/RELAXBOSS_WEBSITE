<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Consent\RecordTrainingConsent;
use App\Enums\TrainingConsentChoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreTrainingConsentRequest;
use Illuminate\Http\RedirectResponse;

/** API-013: catat pilihan persetujuan pelatihan. Dipakai dialog consent dan (TASK-022) halaman Akun. */
final class TrainingConsentController extends Controller
{
    public function __invoke(StoreTrainingConsentRequest $request, RecordTrainingConsent $action): RedirectResponse
    {
        $action->handle(
            $request->user(),
            TrainingConsentChoice::from((string) $request->validated('choice')),
        );

        return redirect()->back(fallback: '/dashboard');
    }
}
