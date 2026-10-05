<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Models\Assessment;
use App\Services\Assessment\ScoringService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * API-008. Validasi di server: semua butir terjawab, nilai sesuai pilihan Instrumen, tanpa butir asing.
 */
final class SubmitAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'Masih ada butir yang belum dijawab.',
            'answers.array' => 'Masih ada butir yang belum dijawab.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Assessment $assessment */
                $assessment = $this->route('activeAssessment');
                $assessment->loadMissing('questions');

                $allowed = app(ScoringService::class)->allowedValues($assessment);
                /** @var array<int|string, mixed> $answers */
                $answers = (array) $this->input('answers');
                $questionIds = $assessment->questions->pluck('id')->all();

                foreach ($questionIds as $id) {
                    if (! array_key_exists($id, $answers) || $answers[$id] === null || $answers[$id] === '') {
                        $validator->errors()->add('answers', 'Masih ada butir yang belum dijawab.');

                        return;
                    }

                    if (! is_numeric($answers[$id]) || ! in_array((int) $answers[$id], $allowed, true)) {
                        $validator->errors()->add('answers', 'Ada jawaban yang tidak valid. Muat ulang halaman lalu coba lagi.');

                        return;
                    }
                }

                if (count(array_diff(array_map('intval', array_keys($answers)), $questionIds)) > 0) {
                    $validator->errors()->add('answers', 'Ada jawaban yang tidak valid. Muat ulang halaman lalu coba lagi.');
                }
            },
        ];
    }

    /**
     * Jawaban dinormalkan ke `{question_id: int}`.
     *
     * @return array<int, int>
     */
    public function answers(): array
    {
        $normalized = [];

        foreach ((array) $this->input('answers') as $id => $value) {
            $normalized[(int) $id] = (int) $value;
        }

        return $normalized;
    }
}
