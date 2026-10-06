<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveAssessmentDefinition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssessmentRequest;
use App\Http\Requests\Admin\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Services\Admin\AssessmentAdminPresenter;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-020 (FR-023, API-017, API-018): kelola Instrumen. Hanya definisi Instrumen;
 * admin tidak melihat jawaban atau hasil pengguna (RULE-042). Tidak ada hapus: Instrumen dinonaktifkan.
 */
final class AssessmentController extends Controller
{
    public function index(AssessmentAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Asesmen/Index', [
            'assessments' => $presenter->list(),
            'status' => session('status'),
        ]);
    }

    public function create(AssessmentAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Asesmen/Form', [
            'form' => $presenter->form(null),
            'status' => null,
        ]);
    }

    public function store(StoreAssessmentRequest $request, SaveAssessmentDefinition $action): RedirectResponse
    {
        $definition = $request->definition();

        $assessment = $action->handle(
            null,
            $definition['assessment'],
            $definition['options'],
            $definition['questions'],
            $definition['rules'],
        );

        return redirect()->route('admin.assessments.edit', $assessment)->with('status', 'assessment-created');
    }

    public function edit(Assessment $assessment, AssessmentAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Asesmen/Form', [
            'form' => $presenter->form($assessment),
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateAssessmentRequest $request,
        Assessment $assessment,
        SaveAssessmentDefinition $action,
    ): RedirectResponse {
        $definition = $request->definition();

        $action->handle(
            $assessment,
            $definition['assessment'],
            $definition['options'],
            $definition['questions'],
            $definition['rules'],
        );

        return redirect()->route('admin.assessments.edit', $assessment)->with('status', 'assessment-updated');
    }
}
