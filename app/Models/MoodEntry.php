<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArousalInput;
use App\Enums\MoodLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-006. Satu catatan mood; satu hari boleh punya beberapa (maks. `limits.mood_per_day`,
 * dijaga aplikasi, bukan indeks unik).
 *
 * `note` terenkripsi (RULE-032). `mood` dan `arousal_input` tidak dienkripsi agar bisa diagregasi.
 * `logged_at` disimpan UTC; `entry_date` adalah tanggal WIB dan hanya boleh dihitung lewat
 * `Support\Wib::date($loggedAt)` (RULE-035).
 * Akses selalu lewat relasi pemilik (RULE-033): $user->moodEntries().
 */
class MoodEntry extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'mood',
        'arousal_input',
        'note',
        'entry_date',
        'logged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mood' => MoodLevel::class,
            'arousal_input' => ArousalInput::class,
            'note' => 'encrypted',
            'entry_date' => 'date',
            'logged_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
