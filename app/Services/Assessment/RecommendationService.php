<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\Contracts\ChatCompletionClient;
use App\DTOs\AssessmentRecommendation;
use App\Enums\AiFeature;
use App\Exceptions\AiUnavailableException;
use App\Exceptions\RecommendationSkippedException;
use App\Models\AssessmentAttempt;
use App\Models\User;
use App\Services\Ai\MajorContext;
use App\Services\Ai\PromptLoader;
use LogicException;

/**
 * Rekomendasi AI untuk hasil Asesmen (FR-010, Architecture 6.5 dan 6.6).
 *
 * Yang dikirim ke penyedia AI hanya: nama Asesmen, nama subskala, tingkat (interpretasi dan
 * keparahan), pertanyaan dan jawaban PFA, serta jurusan (bila diisi). Tanpa nama, email, ID,
 * kampus (RULE-041), dan tanpa angka skor. Jawaban PFA adalah data, bukan instruksi: dikirim
 * sebagai nilai JSON, bukan disisipkan ke teks prompt (RULE-036).
 *
 * Jawaban PFA yang terindikasi krisis tidak pernah dikirim ke AI (RULE-047A).
 * Isi hasil, jawaban, dan rekomendasi tidak masuk log (RULE-040).
 */
final class RecommendationService
{
    public function __construct(
        private readonly ChatCompletionClient $client,
        private readonly PromptLoader $prompts,
        private readonly MajorContext $major,
        private readonly AttemptPresenter $presenter,
        private readonly RecommendationParser $parser,
    ) {}

    /**
     * @throws RecommendationSkippedException bila jawaban PFA terindikasi krisis
     * @throws AiUnavailableException bila AI gagal (pemanggil jatuh ke rekomendasi statis)
     */
    public function generate(User $user, AssessmentAttempt $attempt): AssessmentRecommendation
    {
        if ((int) $attempt->user_id !== (int) $user->getKey()) {
            throw new LogicException('Hasil Asesmen bukan milik pengguna ini.');
        }

        $detail = $this->presenter->detail($attempt);

        if ($detail['crisis'] === true) {
            throw new RecommendationSkippedException('crisis');
        }

        $system = $this->prompts->load(AiFeature::Assessment)->content
            .($this->major->line($user->major) ?? '');

        $completion = $this->client->complete(AiFeature::Assessment, [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $this->payload($detail)],
        ]);

        return $this->parser->parse($completion->content);
    }

    /**
     * @param  array<string, mixed>  $detail  keluaran AttemptPresenter::detail()
     */
    private function payload(array $detail): string
    {
        /** @var list<array<string, mixed>> $subScales */
        $subScales = $detail['sub_scales'];
        /** @var array<string, string> $context */
        $context = $detail['context'] ?? [];
        $maxChars = (int) config('relaxboss.limits.pfa_answer_max_chars');

        $items = [];

        foreach ($subScales as $subScale) {
            $item = [
                'subskala' => (string) $subScale['name'],
                'tingkat' => (string) $subScale['interpretation'],
                'keparahan' => (string) $subScale['severity_level'],
            ];

            $answer = $context[$item['subskala']] ?? null;

            if (is_string($answer) && trim($answer) !== '' && filled($subScale['pfa_question'] ?? null)) {
                $item['konteks'] = [
                    'pertanyaan' => (string) $subScale['pfa_question'],
                    'jawaban' => mb_substr($this->clean($answer), 0, $maxChars),
                ];
            }

            $items[] = $item;
        }

        return json_encode(
            ['asesmen' => (string) $detail['assessment_name'], 'hasil' => $items],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
        );
    }

    private function clean(string $text): string
    {
        return trim((string) preg_replace('/[^\P{C}\n]+/u', '', $text));
    }
}
