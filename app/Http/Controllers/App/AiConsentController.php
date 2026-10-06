<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Consent\GrantAiConsent;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** API-012: catat consent AI versi terbaru, lalu kembali ke halaman asal (dialog lanjut ke langkah 2). */
final class AiConsentController extends Controller
{
    public function __invoke(Request $request, GrantAiConsent $action): RedirectResponse
    {
        $action->handle($request->user());

        return redirect()->back(fallback: '/dashboard');
    }
}
