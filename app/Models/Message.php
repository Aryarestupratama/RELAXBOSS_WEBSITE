<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageRole;
use App\Enums\SuggestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-008. Satu pesan dalam Percakapan. `content` terenkripsi (RULE-032).
 * Tidak ada `user_id`: pemilik dicapai lewat `conversation` (RULE-033).
 * `detected_intent` menyimpan kode enum `Intent` (dibuat di TASK-017), jadi berupa string di sini.
 */
class Message extends Model
{
    /** Tabel hanya punya created_at. */
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'turn_number',
        'role',
        'content',
        'detected_intent',
        'detection_confidence',
        'ai_strategy',
        'ai_flow_action',
        'suggestion_type',
        'is_crisis',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'turn_number' => 'integer',
            'role' => MessageRole::class,
            'content' => 'encrypted',
            'detection_confidence' => 'decimal:4',
            'suggestion_type' => SuggestionType::class,
            'is_crisis' => 'boolean',
        ];
    }

    /** @return BelongsTo<Conversation, $this> */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
