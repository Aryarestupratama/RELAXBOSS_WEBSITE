<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Services\Assessment\PublicAssessmentPresenter;
use Illuminate\Contracts\View\View;

/**
 * Info Asesmen (Blade, dirender di server). FR-006, FR-020, SCR-002.
 * Hanya Asesmen yang boleh tampil Publik; nonaktif atau tidak ada menghasilkan 404.
 */
class PublicAssessmentController extends Controller
{
    public function index(PublicAssessmentPresenter $presenter): View
    {
        $assessments = Assessment::query()
            ->publiclyListed()
            ->with('questions:id,assessment_id,sub_scale')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Assessment $assessment): array => $presenter->listItem($assessment));

        return view('public.asesmen-index', ['assessments' => $assessments]);
    }

    public function show(string $slug, PublicAssessmentPresenter $presenter): View
    {
        $assessment = Assessment::query()
            ->publiclyListed()
            ->with('questions:id,assessment_id,sub_scale')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('public.asesmen-detail', ['assessment' => $presenter->detail($assessment)]);
    }
}
