<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\SpreadsheetAiDataExporter;
use App\DTOs\ExportOptions;
use App\Enums\TrainingConsentChoice;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
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
 *
 * Ekspor Excel (TASK-057): tiga lembar. `Asesmen` (satu baris per hasil), `Hasil_Subskala` (skor per subskala), dan
 * `Jawaban_Butir` (jawaban per butir; `item_no` dari urutan butir Instrumen, kosong bila butirnya sudah diganti admin).
 * `attempt_id` adalah ID acak per ekspor dan satu-satunya penghubung antarlembar.
 */
final class AssessmentExporter implements SpreadsheetAiDataExporter
{
    private const ATTEMPT_COLUMNS = ['id', 'assessment_id', 'answers', 'results', 'user_context', 'ai_recommendation'];

    private const CHUNK_SIZE = 100;

    /** @var array<string, array<int, int>> slug Instrumen => [id butir => urutan butir]; diisi saat dibutuhkan. */
    private array $itemPositions = [];

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
            ->when($options->until !== null, fn (Builder $query) => $query->where('completed_at', '<', $options->until))
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

    public function sheets(): array
    {
        return [
            'Asesmen' => [
                'attempt_id' => 18,
                'assessment' => 20,
                'is_crisis' => 10,
                'subscale_summary' => 45,
                'pfa_status' => 14,
                'pfa_answers' => 60,
                'ai_recommendation' => 90,
            ],
            'Hasil_Subskala' => [
                'attempt_id' => 18,
                'assessment' => 20,
                'subscale' => 28,
                'score' => 8,
                'interpretation' => 22,
                'severity_level' => 15,
                'trigger_pfa' => 12,
            ],
            'Jawaban_Butir' => [
                'attempt_id' => 18,
                'assessment' => 20,
                'question_id' => 12,
                'item_no' => 9,
                'value' => 8,
            ],
        ];
    }

    public function rows(string $recordId, array $record): array
    {
        $slug = is_string($record['assessment'] ?? null) ? $record['assessment'] : null;
        $results = is_array($record['results'] ?? null) ? $record['results'] : [];
        $answers = is_array($record['answers'] ?? null) ? $record['answers'] : [];
        $pfa = $record['pfa_answers'] ?? null;

        $summary = [];
        $subscales = [];

        foreach ($results as $name => $result) {
            $result = is_array($result) ? $result : [];
            $summary[] = $name.': '.($result['score'] ?? '-').' ('.($result['interpretation'] ?? '-').')';
            $subscales[] = [
                $recordId,
                $slug,
                (string) $name,
                $result['score'] ?? null,
                $result['interpretation'] ?? null,
                $result['severity_level'] ?? null,
                isset($result['trigger_pfa']) ? (bool) $result['trigger_pfa'] : null,
            ];
        }

        $positions = $this->positionsFor($slug);
        $items = [];

        foreach ($answers as $questionId => $value) {
            $items[] = [$recordId, $slug, (int) $questionId, $positions[(int) $questionId] ?? null, is_numeric($value) ? (int) $value : null];
        }

        // Urut menurut butir supaya enak dibaca; butir tanpa urutan (sudah diganti) di akhir.
        usort($items, static fn (array $a, array $b): int => ($a[3] ?? PHP_INT_MAX) <=> ($b[3] ?? PHP_INT_MAX) ?: $a[2] <=> $b[2]);

        return [
            'Asesmen' => [[
                $recordId,
                $slug,
                (bool) ($record['is_crisis'] ?? false),
                $summary === [] ? null : implode("\n", $summary),
                // null = belum memutuskan, [] = dilewati, objek = dijawab (sama seperti `pfa_answers` di JSONL).
                $pfa === null ? 'undecided' : ($pfa === [] ? 'skipped' : 'answered'),
                is_array($pfa) && $pfa !== [] ? $this->joinPfa($pfa) : null,
                $record['ai_recommendation'] ?? null,
            ]],
            'Hasil_Subskala' => $subscales,
            'Jawaban_Butir' => $items,
        ];
    }

    /** @param  array<array-key, mixed>  $pfa */
    private function joinPfa(array $pfa): string
    {
        $lines = [];

        foreach ($pfa as $subscale => $text) {
            $lines[] = $subscale.': '.(is_string($text) ? $text : json_encode($text, JSON_UNESCAPED_UNICODE));
        }

        return implode("\n", $lines);
    }

    /**
     * Urutan butir per Instrumen (cache per slug). Butir yang sudah dihapus atau diganti admin tidak ada di sini,
     * sehingga `item_no` kosong untuk jawaban lama tersebut.
     *
     * @return array<int, int>
     */
    private function positionsFor(?string $slug): array
    {
        if ($slug === null) {
            return [];
        }

        return $this->itemPositions[$slug] ??= AssessmentQuestion::query()
            ->whereIn('assessment_id', Assessment::query()->where('slug', $slug)->select('id'))
            ->pluck('position', 'id')
            ->map(static fn (mixed $position): int => (int) $position)
            ->all();
    }
}
