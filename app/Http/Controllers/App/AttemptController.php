<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Assessment\SaveAssessmentContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreAssessmentContextRequest;
use App\Models\AssessmentAttempt;
use App\Services\Assessment\AttemptPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Hasil dan riwayat Asesmen. Semua data lewat relasi pemilik (RULE-033). */
final class AttemptController extends Controller
{
    /** SCR-015: riwayat, terbaru dulu (FR-009). */
    public function index(Request $request, AttemptPresenter $presenter): Response
    {
        $attempts = $request->user()
            ->assessmentAttempts()
            ->with('assessment')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->simplePaginate((int) config('relaxboss.limits.history_per_page'));

        return Inertia::render('App/Riwayat/Index', [
            'attempts' => $attempts->getCollection()
                ->map(fn (AssessmentAttempt $attempt): array => $presenter->summary($attempt))
                ->values(),
            'prev_page_url' => $attempts->previousPageUrl(),
            'next_page_url' => $attempts->nextPageUrl(),
        ]);
    }

    /** SCR-014: hasil per subskala, langkah PFA, rekomendasi statis (FR-008). */
    public function show(Request $request, int $attempt, AttemptPresenter $presenter): Response
    {
        $record = $request->user()->assessmentAttempts()->findOrFail($attempt);

        return Inertia::render('App/Riwayat/Show', [
            'attempt' => $presenter->detail($record),
            'max_answer_chars' => (int) config('relaxboss.limits.pfa_answer_max_chars'),
        ]);
    }

    /** API-009: simpan jawaban PFA (opsional; kosong = lewati). Sekali saja per hasil. */
    public function storeContext(StoreAssessmentContextRequest $request, SaveAssessmentContext $action): RedirectResponse
    {
        $attempt = $request->attempt();

        if ($attempt->user_context !== null) {
            return redirect()->route('app.attempts.show', $attempt->id);
        }

        $action->handle($attempt, $request->answers());

        return redirect()->route('app.attempts.show', $attempt->id);
    }
}
