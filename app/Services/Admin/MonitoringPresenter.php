<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\Intent;
use App\Enums\MessageRole;
use App\Models\AdminAccessLog;
use App\Models\AssessmentAttempt;
use App\Models\Conversation;
use App\Models\Message;
use App\Support\PseudoId;
use App\Support\Wib;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Pagination\Paginator;

/**
 * Tinjauan admin tanpa identitas (FR-031, RULE-042, SCR-022).
 *
 * Aturan yang dijaga di sini:
 * - tidak ada join atau relasi ke `users`, dan kolom `user_id` tidak pernah dipilih;
 * - hanya tanggal (WIB, tanpa jam) dan ID semu;
 * - Asesmen: hanya hasil per subskala dan rekomendasi AI. `answers` dan `user_context` (jawaban PFA)
 *   tidak pernah dipilih, dan `ai_summary` sengaja tidak ditampilkan karena meringkas konteks pengguna.
 * Daftar hanya memuat metadata; isi dibuka lewat halaman detail yang mencatat `admin_access_logs`.
 */
final class MonitoringPresenter
{
    public const CHAT_FILTERS = ['krisis', 'rendah', 'acak'];

    public const ATTEMPT_FILTERS = ['ai', 'semua'];

    private const CONVERSATION_COLUMNS = ['id', 'initial_intent', 'total_turns', 'has_crisis', 'last_message_at', 'created_at'];

    private const SEVERITY_LABELS = [
        'normal' => 'Normal',
        'mild' => 'Ringan',
        'moderate' => 'Sedang',
        'severe' => 'Berat',
    ];

    private const SUGGESTION_LABELS = [
        'escalate_crisis' => 'Saran: bantuan krisis',
        'recommend_professional' => 'Saran: temui profesional',
        'recommend_support' => 'Saran: cari dukungan',
    ];

    public function normalizeChatFilter(mixed $value): string
    {
        return is_string($value) && in_array($value, self::CHAT_FILTERS, true) ? $value : 'krisis';
    }

    public function normalizeAttemptFilter(mixed $value): string
    {
        return is_string($value) && in_array($value, self::ATTEMPT_FILTERS, true) ? $value : 'ai';
    }

    /**
     * Daftar Percakapan (metadata saja, tanpa isi pesan dan tanpa pemilik).
     *
     * @return array{rows: list<array<string, mixed>>, prev_page_url: string|null, next_page_url: string|null}
     */
    public function conversations(string $filter): array
    {
        $query = Conversation::query()
            ->select(self::CONVERSATION_COLUMNS)
            ->withMin('messages', 'detection_confidence')
            ->has('messages');

        if ($filter === 'krisis') {
            $query->where('has_crisis', true);
        } elseif ($filter === 'rendah') {
            $threshold = (float) config('relaxboss.admin.monitoring.low_confidence');
            $query->whereHas('messages', static function ($messages) use ($threshold): void {
                $messages->reorder()
                    ->whereNotNull('detection_confidence')
                    ->where('detection_confidence', '<', $threshold);
            });
        }

        if ($filter === 'acak') {
            $items = $query->inRandomOrder()->limit((int) config('relaxboss.admin.monitoring.sample_size'))->get();

            return ['rows' => $this->conversationRows($items->all()), 'prev_page_url' => null, 'next_page_url' => null];
        }

        /** @var Paginator<int, Conversation> $page */
        $page = $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->simplePaginate((int) config('relaxboss.admin.monitoring.per_page'))
            ->withQueryString();

        return [
            'rows' => $this->conversationRows($page->items()),
            'prev_page_url' => $page->previousPageUrl(),
            'next_page_url' => $page->nextPageUrl(),
        ];
    }

    /**
     * Satu Percakapan beserta pesannya, atau null bila tidak ada. Dipanggil setelah pembukaan dicatat.
     *
     * @return array{conversation: array<string, mixed>, messages: list<array<string, mixed>>}|null
     */
    public function conversation(string $id): ?array
    {
        $conversation = Conversation::query()
            ->select(self::CONVERSATION_COLUMNS)
            ->whereKey($id)
            ->first();

        if ($conversation === null) {
            return null;
        }

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->orderBy('turn_number')
            ->get(['id', 'turn_number', 'role', 'content', 'detected_intent', 'detection_confidence', 'suggestion_type', 'is_crisis']);

        $threshold = (float) config('relaxboss.admin.monitoring.low_confidence');

        return [
            'conversation' => [
                'pseudo_id' => PseudoId::for(AdminAccessLog::RESOURCE_CONVERSATION, $conversation->id),
                'date' => $this->dateOf($conversation),
                'total_turns' => $conversation->total_turns,
                'has_crisis' => $conversation->has_crisis,
                'initial_intent' => $this->intentLabel($conversation->initial_intent),
            ],
            'messages' => $messages
                ->map(function (Message $message) use ($threshold): array {
                    $confidence = $message->detection_confidence === null ? null : (float) $message->detection_confidence;

                    return [
                        'id' => $message->id,
                        'role' => $message->role === MessageRole::User ? 'user' : 'assistant',
                        'content' => $this->readContent($message),
                        'intent' => $this->intentLabel($message->detected_intent),
                        'confidence' => $confidence === null ? null : (int) round($confidence * 100),
                        'low_confidence' => $confidence !== null && $confidence < $threshold,
                        'is_crisis' => $message->is_crisis,
                        'suggestion' => $message->suggestion_type === null
                            ? null
                            : (self::SUGGESTION_LABELS[$message->suggestion_type->value] ?? null),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /**
     * Daftar hasil Asesmen (metadata saja). `ai`: hanya yang punya Rekomendasi AI.
     *
     * @return array{rows: list<array<string, mixed>>, prev_page_url: string|null, next_page_url: string|null}
     */
    public function attempts(string $filter): array
    {
        $query = AssessmentAttempt::query()
            ->select(['id', 'assessment_id', 'completed_at'])
            ->selectRaw('(ai_recommendation is not null) as has_ai_recommendation')
            ->with('assessment:id,name,display_name');

        if ($filter === 'ai') {
            $query->whereNotNull('ai_recommendation');
        }

        /** @var Paginator<int, AssessmentAttempt> $page */
        $page = $query
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->simplePaginate((int) config('relaxboss.admin.monitoring.per_page'))
            ->withQueryString();

        $rows = collect($page->items())
            ->map(fn (AssessmentAttempt $attempt): array => [
                'id' => $attempt->id,
                'pseudo_id' => PseudoId::for(AdminAccessLog::RESOURCE_ATTEMPT, $attempt->id),
                'date' => Wib::date($attempt->completed_at),
                'assessment' => $this->assessmentName($attempt),
                'has_ai_recommendation' => (bool) $attempt->getAttribute('has_ai_recommendation'),
            ])
            ->values()
            ->all();

        return ['rows' => $rows, 'prev_page_url' => $page->previousPageUrl(), 'next_page_url' => $page->nextPageUrl()];
    }

    /**
     * Satu hasil Asesmen: hasil per subskala dan Rekomendasi AI. Tanpa jawaban per butir dan tanpa jawaban PFA.
     *
     * @return array<string, mixed>|null
     */
    public function attempt(int $id): ?array
    {
        $attempt = AssessmentAttempt::query()
            ->select(['id', 'assessment_id', 'results', 'ai_recommendation', 'completed_at'])
            ->with('assessment:id,name,display_name')
            ->whereKey($id)
            ->first();

        if ($attempt === null) {
            return null;
        }

        /** @var array<string, array{score: int, interpretation: string, severity_level: string}> $results */
        $results = $attempt->results;

        return [
            'pseudo_id' => PseudoId::for(AdminAccessLog::RESOURCE_ATTEMPT, $attempt->id),
            'date' => Wib::date($attempt->completed_at),
            'assessment' => $this->assessmentName($attempt),
            'sub_scales' => collect($results)
                ->map(static fn (array $result, string $name): array => [
                    'name' => $name,
                    'score' => $result['score'],
                    'interpretation' => $result['interpretation'],
                    'severity' => self::SEVERITY_LABELS[$result['severity_level']] ?? $result['severity_level'],
                ])
                ->values()
                ->all(),
            'recommendation' => $this->readRecommendation($attempt),
        ];
    }

    /**
     * @param  array<int, Conversation>  $items
     * @return list<array<string, mixed>>
     */
    private function conversationRows(array $items): array
    {
        $threshold = (float) config('relaxboss.admin.monitoring.low_confidence');

        return collect($items)
            ->map(function (Conversation $conversation) use ($threshold): array {
                $lowest = $conversation->getAttribute('messages_min_detection_confidence');
                $lowest = $lowest === null ? null : (float) $lowest;

                return [
                    'id' => $conversation->id,
                    'pseudo_id' => PseudoId::for(AdminAccessLog::RESOURCE_CONVERSATION, $conversation->id),
                    'date' => $this->dateOf($conversation),
                    'total_turns' => $conversation->total_turns,
                    'initial_intent' => $this->intentLabel($conversation->initial_intent),
                    'has_crisis' => $conversation->has_crisis,
                    'lowest_confidence' => $lowest === null ? null : (int) round($lowest * 100),
                    'low_confidence' => $lowest !== null && $lowest < $threshold,
                ];
            })
            ->values()
            ->all();
    }

    /** Hanya tanggal WIB, tanpa jam (RULE-042). */
    private function dateOf(Conversation $conversation): string
    {
        return Wib::date($conversation->last_message_at ?? $conversation->created_at);
    }

    private function intentLabel(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return Intent::tryFrom($code)?->label() ?? $code;
    }

    private function assessmentName(AssessmentAttempt $attempt): string
    {
        $assessment = $attempt->assessment;

        return $assessment === null ? 'Asesmen' : ($assessment->display_name ?: $assessment->name);
    }

    /** Isi terenkripsi yang tidak bisa dibaca (kunci berubah) tidak boleh menjatuhkan halaman. */
    private function readContent(Message $message): string
    {
        try {
            return (string) $message->content;
        } catch (DecryptException) {
            return '[Isi tidak dapat dibaca]';
        }
    }

    private function readRecommendation(AssessmentAttempt $attempt): ?string
    {
        try {
            $value = $attempt->ai_recommendation;
        } catch (DecryptException) {
            return '[Isi tidak dapat dibaca]';
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
