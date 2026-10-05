<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Assessment\SaveAssessmentAttempt;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\SubmitAssessmentRequest;
use App\Models\Assessment;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AssessmentController extends Controller
{
    /** SCR-012: katalog Asesmen aktif (FR-006). */
    public function index(): Response
    {
        $assessments = Assessment::query()
            ->active()
            ->withCount('questions')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Assessment $assessment): array => [
                'slug' => $assessment->slug,
                'name' => $assessment->display_name ?? $assessment->name,
                'description' => $assessment->description,
                'estimated_minutes' => $assessment->estimated_minutes,
                'question_count' => $assessment->questions_count,
                'is_validated' => $assessment->isValidated(),
            ]);

        return Inertia::render('App/Asesmen/Index', ['assessments' => $assessments]);
    }

    /** SCR-013: pengerjaan, satu butir per layar. Skor tidak pernah dikirim ke klien. */
    public function show(Assessment $activeAssessment): Response
    {
        $activeAssessment->load('questions');

        return Inertia::render('App/Asesmen/Kerjakan', [
            'assessment' => [
                'slug' => $activeAssessment->slug,
                'name' => $activeAssessment->display_name ?? $activeAssessment->name,
                'instructions' => $activeAssessment->instructions,
                'options' => collect($activeAssessment->options)
                    ->map(fn (array $option): array => [
                        'value' => (int) $option['value'],
                        'label' => (string) $option['label'],
                    ])
                    ->values(),
                'questions' => $activeAssessment->questions
                    ->map(fn ($question): array => [
                        'id' => $question->id,
                        'text' => $question->text,
                    ])
                    ->values(),
            ],
        ]);
    }

    /** API-008: simpan jawaban, hitung skor di server. */
    public function store(
        SubmitAssessmentRequest $request,
        Assessment $activeAssessment,
        SaveAssessmentAttempt $action,
    ): RedirectResponse {
        $attempt = $action->handle($request->user(), $activeAssessment, $request->answers());

        return redirect()->route('app.attempts.show', $attempt);
    }
}
