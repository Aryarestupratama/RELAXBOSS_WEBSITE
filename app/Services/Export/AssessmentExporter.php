<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\AiDataExporter;
use App\DTOs\ExportOptions;
use App\Enums\TrainingConsentChoice;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\Chat\CrisisDetector;
use Illuminate\Database\Eloquent\Builder;

/**
 * Eksportir fitur `assessment` (FR-030): satu record per hasil Asesmen yang selesai, berisi slug
 * Instrumen, jawaban per butir, hasil per subskala, jawaban PFA, dan rekomendasi AI (Architecture bagian 9).
 *
 * Penjaga identitas (RULE-070): hanya pengguna `granted` (subquery, bukan join); kolom dipilih eksplisit
 * sehingga `user_id` tidak dimuat; tanpa tanggal. `ai_summary` sengaja tidak diekspor (tidak ada di
 * daftar Architecture bagian 9; meringkas konteks pengguna).
 *
 * `is_crisis` dihitung dari jawaban PFA lewat `CrisisDetector` (tanpa kolom di database, Schema v1.1),
 * jadi `--exclude-crisis` untuk fitur ini ditangani perintah dari nilai `is_crisis` pada record.
 */
final class AssessmentExporter implements AiDataExporter
{
    private const ATTEMPT_COLUMNS = ['id', 'assessment_id', 'answers', 'results', 'user_context', 'ai_recommendation'];

    private const CHUNK_SIZE = 100;

    public function __construct(
        private readonly ContactScrubber $scrubber,
        private readonly CrisisDetector $crisis,
    ) {}

    public function feature(): string
    {
        return 'assessment';
    }

    public function query(ExportOptions $options): iterable
    {
        return AssessmentAttempt::query()
            ->select(self::ATTEMPT_COLUMNS)
            ->whereIn('user_id', User::query()
                ->where('ai_training_consent_choice', TrainingConsentChoice::Granted->value)
                ->select('id'))
            ->when($options->since !== null, fn (Builder $query) => $query->where('completed_at', '>=', $options->since))
            ->with(['assessment' => fn ($relation) => $relation->select(['id', 'slug'])])
            ->lazyById(self::CHUNK_SIZE, 'id');
    }

    public function toRecord(object $row): array
    {
        /** @var AssessmentAttempt $row */
        /** @var array<string, mixed>|null $context */
        $context = $row->user_context;

        return [
            'assessment' => $row->assessment->slug ?? null,
            'is_crisis' => $this->hasCrisis($context),
            // Jawaban mentah per butir ({id butir: nilai}), seperti yang disimpan.
            'answers' => $row->answers,
            'results' => $row->results,
            // null = belum memutuskan, [] = dilewati, objek = jawaban PFA per subskala.
            'pfa_answers' => $this->scrubber->scrubDeep($context),
            'ai_recommendation' => $this->scrubber->scrub($row->ai_recommendation),
        ];
    }

    /** @param  array<string, mixed>|null  $context */
    private function hasCrisis(?array $context): bool
    {
        foreach ($context ?? [] as $answer) {
            if (is_string($answer) && $this->crisis->detect($answer) !== null) {
                return true;
            }
        }

        return false;
    }
}
