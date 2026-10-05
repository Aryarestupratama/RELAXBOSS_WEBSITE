<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Models\AssessmentAttempt;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * API-009. Jawaban PFA bersifat opsional: semua kolom boleh kosong (itu artinya "Lewati").
 * Kunci hanya boleh subskala yang memicu PFA pada hasil ini. Hasil milik orang lain menghasilkan 404.
 */
final class StoreAssessmentContextRequest extends FormRequest
{
    private ?AssessmentAttempt $attempt = null;

    public function authorize(): bool
    {
        return $this->attempt() instanceof AssessmentAttempt;
    }

    public function attempt(): AssessmentAttempt
    {
        // Lewat relasi pemilik (RULE-033); findOrFail memberi 404 untuk milik orang lain.
        return $this->attempt ??= $this->user()->assessmentAttempts()->findOrFail($this->route('attempt'));
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $max = (int) config('relaxboss.limits.pfa_answer_max_chars');

        return [
            'answers' => ['present', 'array'],
            'answers.*' => ['nullable', 'string', "max:{$max}"],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $max = (int) config('relaxboss.limits.pfa_answer_max_chars');

        return [
            'answers.present' => 'Jawaban tidak valid. Muat ulang halaman lalu coba lagi.',
            'answers.array' => 'Jawaban tidak valid. Muat ulang halaman lalu coba lagi.',
            'answers.*.string' => 'Jawaban tidak valid. Muat ulang halaman lalu coba lagi.',
            'answers.*.max' => "Jawaban terlalu panjang. Maksimal {$max} karakter.",
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var array<string, mixed> $results */
                $results = $this->attempt()->results;
                $allowed = collect($results)
                    ->filter(static fn (array $result): bool => (bool) $result['trigger_pfa'])
                    ->keys()
                    ->all();

                foreach (array_keys((array) $this->input('answers')) as $key) {
                    if (! in_array((string) $key, $allowed, true)) {
                        $validator->errors()->add('answers', 'Jawaban tidak valid. Muat ulang halaman lalu coba lagi.');

                        return;
                    }
                }
            },
        ];
    }

    /**
     * Hanya jawaban yang terisi, tanpa spasi tepi. Kosong total berarti pengguna melewati.
     *
     * @return array<string, string>
     */
    public function answers(): array
    {
        $clean = [];

        foreach ((array) $this->input('answers') as $subScale => $text) {
            $text = trim((string) $text);
            if ($text !== '') {
                $clean[(string) $subScale] = $text;
            }
        }

        return $clean;
    }
}
