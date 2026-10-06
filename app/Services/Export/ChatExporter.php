<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\AiDataExporter;
use App\DTOs\ExportOptions;
use App\Enums\TrainingConsentChoice;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Eksportir fitur `chat` (FR-030): satu record per Percakapan dengan pesan terurut.
 *
 * Penjaga identitas (RULE-070): hanya pengguna `granted` (lewat subquery, bukan join); kolom dipilih
 * eksplisit sehingga `user_id` tidak pernah dimuat; tanpa tanggal, jurusan, atau kampus.
 */
final class ChatExporter implements AiDataExporter
{
    private const CONVERSATION_COLUMNS = ['id', 'initial_intent', 'has_crisis'];

    private const MESSAGE_COLUMNS = [
        'id', 'conversation_id', 'turn_number', 'role', 'content',
        'detected_intent', 'detection_confidence', 'suggestion_type', 'is_crisis',
    ];

    private const CHUNK_SIZE = 100;

    public function __construct(private readonly ContactScrubber $scrubber) {}

    public function feature(): string
    {
        return 'chat';
    }

    public function query(ExportOptions $options): iterable
    {
        return Conversation::query()
            ->select(self::CONVERSATION_COLUMNS)
            ->whereIn('user_id', User::query()
                ->where('ai_training_consent_choice', TrainingConsentChoice::Granted->value)
                ->select('id'))
            ->when($options->since !== null, fn (Builder $query) => $query->where('created_at', '>=', $options->since))
            ->when($options->excludeCrisis, fn (Builder $query) => $query->where('has_crisis', false))
            ->with(['messages' => fn ($relation) => $relation->select(self::MESSAGE_COLUMNS)])
            ->lazyById(self::CHUNK_SIZE, 'id');
    }

    public function toRecord(object $row): array
    {
        /** @var Conversation $row */
        $messages = [];

        /** @var Message $message */
        foreach ($row->messages as $message) {
            $messages[] = [
                'role' => $message->role->value,
                'content' => $this->scrubber->scrub($message->content),
                'detected_intent' => $message->detected_intent,
                'detection_confidence' => $message->detection_confidence === null ? null : (float) $message->detection_confidence,
                'suggestion_type' => $message->suggestion_type?->value,
                'is_crisis' => $message->is_crisis,
            ];
        }

        return [
            'initial_intent' => $row->initial_intent,
            'is_crisis' => $row->has_crisis,
            'messages' => $messages,
        ];
    }
}
