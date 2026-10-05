<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * ENT-007. Satu Percakapan RelaxMate. ID berupa UUID v4.
 * Tidak ada judul, ringkasan, atau data riset. Isi pesan ada di `Message` (terenkripsi).
 * Akses selalu lewat relasi pemilik (RULE-033): $user->conversations().
 */
class Conversation extends Model
{
    use HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'status',
        'initial_intent',
        'total_turns',
        'has_crisis',
        'last_message_at',
    ];

    /** Nilai awal agar model baru konsisten dengan default database. */
    protected $attributes = [
        'status' => 'active',
        'total_turns' => 0,
        'has_crisis' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'total_turns' => 'integer',
            'has_crisis' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    /** UUID v4 sesuai Schema (bawaan trait adalah v7). */
    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Message, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('turn_number');
    }
}
