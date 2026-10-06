<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Assessment;

/** API-018: ubah Asesmen. Slug tidak ikut diubah (tautan Publik tidak boleh putus). */
final class UpdateAssessmentRequest extends AssessmentFormRequest
{
    protected function existingQuestionIds(): array
    {
        /** @var Assessment $assessment */
        $assessment = $this->route('assessment');

        /** @var list<int> $ids */
        $ids = $assessment->questions()->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        return $ids;
    }
}
