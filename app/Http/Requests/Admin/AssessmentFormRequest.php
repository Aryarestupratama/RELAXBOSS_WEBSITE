<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\SeverityLevel;
use App\Services\Assessment\ScoringRangeValidator;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * API-017 dan API-018 (FR-023): validasi bersama pembuatan dan perubahan Instrumen beserta
 * pilihan jawaban, butir, dan aturan skor. Semua divalidasi di server (RULE-023).
 */
abstract class AssessmentFormRequest extends FormRequest
{
    /** Teks bidang untuk pesan galat: jalur => [label, jenis]. `s` = teks, `n` = angka. */
    private const FIELDS = [
        'name' => ['Nama resmi', 's'],
        'display_name' => ['Nama ramah', 's'],
        'description' => ['Deskripsi', 's'],
        'instructions' => ['Petunjuk', 's'],
        'estimated_minutes' => ['Perkiraan durasi', 'n'],
        'score_multiplier' => ['Pengali skor', 'n'],
        'source_reference' => ['Sumber dan izin', 's'],
        'creator_name' => ['Nama pembuat', 's'],
        'creator_institution' => ['Institusi pembuat', 's'],
        'validator_name' => ['Nama validator', 's'],
        'validator_credential' => ['Kredensial validator', 's'],
        'sort_order' => ['Urutan', 'n'],
        'options.*.value' => ['Nilai pilihan', 'n'],
        'options.*.label' => ['Label pilihan', 's'],
        'questions.*.text' => ['Teks butir', 's'],
        'questions.*.sub_scale' => ['Subskala', 's'],
        'rules.*.sub_scale' => ['Subskala', 's'],
        'rules.*.min_score' => ['Skor minimal', 'n'],
        'rules.*.max_score' => ['Skor maksimal', 'n'],
        'rules.*.interpretation' => ['Interpretasi', 's'],
        'rules.*.pfa_question' => ['Pertanyaan PFA', 's'],
        'rules.*.static_recommendation' => ['Rekomendasi statis', 's'],
    ];

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /** ID butir yang sudah ada pada Instrumen ini (kosong saat membuat). @return list<int> */
    abstract protected function existingQuestionIds(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $limits = (array) config('relaxboss.admin.assessment');
        $maxScore = ScoringRangeValidator::SCORE_LIMIT;

        return [
            'name' => ['required', 'string', 'max:150'],
            'display_name' => ['nullable', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:255'],
            'score_multiplier' => ['required', 'numeric', 'min:0.01', 'max:99.99'],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'creator_name' => ['nullable', 'string', 'max:150'],
            'creator_institution' => ['nullable', 'string', 'max:150'],
            'validator_name' => ['nullable', 'string', 'max:150'],
            'validator_credential' => ['nullable', 'string', 'max:150'],
            'validated_at' => ['nullable', 'date', 'before_or_equal:today'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:'.$maxScore],

            'options' => ['required', 'array', 'min:2', 'max:'.(int) $limits['max_options']],
            'options.*.value' => ['required', 'integer', 'min:0', 'max:'.(int) $limits['max_option_value'], 'distinct'],
            'options.*.label' => ['required', 'string', 'max:100'],

            'questions' => ['required', 'array', 'min:1', 'max:'.(int) $limits['max_questions']],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.text' => ['required', 'string', 'max:500'],
            'questions.*.sub_scale' => ['required', 'string', 'max:50'],
            'questions.*.is_reversed' => ['required', 'boolean'],

            'rules' => ['required', 'array', 'min:1', 'max:'.(int) $limits['max_rules']],
            'rules.*.sub_scale' => ['required', 'string', 'max:50'],
            'rules.*.min_score' => ['required', 'integer', 'min:0', 'max:'.$maxScore],
            'rules.*.max_score' => ['required', 'integer', 'min:0', 'max:'.$maxScore],
            'rules.*.interpretation' => ['required', 'string', 'max:100'],
            'rules.*.severity_level' => ['required', Rule::enum(SeverityLevel::class)],
            'rules.*.trigger_pfa' => ['required', 'boolean'],
            'rules.*.pfa_question' => ['nullable', 'string', 'max:500'],
            'rules.*.static_recommendation' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $limits = (array) config('relaxboss.admin.assessment');

        $messages = [
            'validated_at.date' => 'Tanggal validasi belum benar.',
            'validated_at.before_or_equal' => 'Tanggal validasi tidak boleh di masa depan.',
            'is_active.required' => 'Status aktif wajib dipilih.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'options.required' => 'Pilihan jawaban wajib diisi.',
            'options.min' => 'Pilihan jawaban minimal 2.',
            'options.max' => 'Pilihan jawaban maksimal '.(int) $limits['max_options'].'.',
            'options.*.value.distinct' => 'Nilai pilihan tidak boleh sama.',
            'options.*.value.min' => 'Nilai pilihan minimal 0.',
            'options.*.value.max' => 'Nilai pilihan maksimal '.(int) $limits['max_option_value'].'.',
            'questions.required' => 'Butir wajib diisi.',
            'questions.min' => 'Minimal ada 1 butir.',
            'questions.max' => 'Butir maksimal '.(int) $limits['max_questions'].'.',
            'questions.*.id.integer' => 'Butir tidak dikenal.',
            'questions.*.is_reversed.required' => 'Tandai apakah butir ini terbalik.',
            'questions.*.is_reversed.boolean' => 'Penanda butir terbalik tidak valid.',
            'rules.required' => 'Aturan skor wajib diisi.',
            'rules.min' => 'Minimal ada 1 aturan skor.',
            'rules.max' => 'Aturan skor maksimal '.(int) $limits['max_rules'].'.',
            'rules.*.severity_level.required' => 'Tingkat keparahan wajib dipilih.',
            'rules.*.severity_level.enum' => 'Tingkat keparahan tidak valid. Muat ulang halaman lalu coba lagi.',
            'rules.*.trigger_pfa.required' => 'Tandai apakah aturan ini memicu pertanyaan PFA.',
            'rules.*.trigger_pfa.boolean' => 'Penanda PFA tidak valid.',
        ];

        foreach (self::FIELDS as $path => [$label, $kind]) {
            $messages["{$path}.required"] = "{$label} wajib diisi.";
            $messages["{$path}.string"] = "{$label} harus berupa teks.";
            $messages["{$path}.integer"] = "{$label} harus berupa bilangan bulat.";
            $messages["{$path}.numeric"] = "{$label} harus berupa angka.";
            $messages["{$path}.max"] = $kind === 's'
                ? "{$label} maksimal :max karakter."
                : "{$label} maksimal :max.";
            $messages["{$path}.min"] = $kind === 's'
                ? "{$label} minimal :min karakter."
                : "{$label} minimal :min.";
        }

        return $messages;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->checkQuestionIds($validator);
                $this->checkPfaQuestions($validator);

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->checkScoringRanges($validator);
            },
        ];
    }

    /**
     * Data terstruktur yang sudah tervalidasi, siap disimpan Action.
     *
     * @return array{
     *     assessment: array<string, mixed>,
     *     options: list<array{value: int, label: string}>,
     *     questions: list<array{id: int|null, text: string, sub_scale: string, is_reversed: bool}>,
     *     rules: list<array{sub_scale: string, min_score: int, max_score: int, interpretation: string, severity_level: string, trigger_pfa: bool, pfa_question: string|null, static_recommendation: string}>
     * }
     */
    public function definition(): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->validated();

        $options = [];
        foreach ((array) $data['options'] as $option) {
            $options[] = ['value' => (int) $option['value'], 'label' => (string) $option['label']];
        }

        $questions = [];
        foreach ((array) $data['questions'] as $question) {
            $questions[] = [
                'id' => isset($question['id']) ? (int) $question['id'] : null,
                'text' => (string) $question['text'],
                'sub_scale' => (string) $question['sub_scale'],
                'is_reversed' => (bool) $question['is_reversed'],
            ];
        }

        $rules = [];
        foreach ((array) $data['rules'] as $rule) {
            $triggerPfa = (bool) $rule['trigger_pfa'];
            $rules[] = [
                'sub_scale' => (string) $rule['sub_scale'],
                'min_score' => (int) $rule['min_score'],
                'max_score' => (int) $rule['max_score'],
                'interpretation' => (string) $rule['interpretation'],
                'severity_level' => (string) $rule['severity_level'],
                'trigger_pfa' => $triggerPfa,
                'pfa_question' => $triggerPfa ? (string) $rule['pfa_question'] : null,
                'static_recommendation' => (string) $rule['static_recommendation'],
            ];
        }

        $assessment = collect($data)
            ->except(['options', 'questions', 'rules'])
            ->all();

        return [
            'assessment' => $assessment,
            'options' => $options,
            'questions' => $questions,
            'rules' => $rules,
        ];
    }

    /** ID butir hanya boleh milik Instrumen ini dan tidak boleh ganda. */
    private function checkQuestionIds(Validator $validator): void
    {
        $known = $this->existingQuestionIds();
        $seen = [];

        foreach ((array) $this->input('questions') as $index => $question) {
            $id = is_array($question) ? ($question['id'] ?? null) : null;

            if ($id === null || $id === '') {
                continue;
            }

            if (! in_array((int) $id, $known, true) || isset($seen[(int) $id])) {
                $validator->errors()->add("questions.{$index}.id", 'Butir tidak dikenal.');

                continue;
            }

            $seen[(int) $id] = true;
        }
    }

    /** Pertanyaan PFA wajib terisi bila aturan memicu PFA (Schema ENT-004). */
    private function checkPfaQuestions(Validator $validator): void
    {
        foreach ((array) $this->input('rules') as $index => $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $trigger = filter_var($rule['trigger_pfa'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($trigger && ! filled($rule['pfa_question'] ?? null)) {
                $validator->errors()->add("rules.{$index}.pfa_question", 'Pertanyaan PFA wajib diisi bila aturan ini memicu PFA.');
            }
        }
    }

    private function checkScoringRanges(Validator $validator): void
    {
        $optionValues = [];
        foreach ((array) $this->input('options') as $option) {
            $optionValues[] = (int) $option['value'];
        }

        $subScales = [];
        foreach ((array) $this->input('questions') as $question) {
            $subScales[] = (string) $question['sub_scale'];
        }

        $rules = [];
        foreach ((array) $this->input('rules') as $index => $rule) {
            $rules[(int) $index] = [
                'sub_scale' => (string) $rule['sub_scale'],
                'min_score' => (int) $rule['min_score'],
                'max_score' => (int) $rule['max_score'],
            ];
        }

        $errors = app(ScoringRangeValidator::class)->validate(
            $optionValues,
            (float) $this->input('score_multiplier'),
            $subScales,
            $rules,
        );

        foreach ($errors as $path => $message) {
            $validator->errors()->add($path, $message);
        }
    }
}
