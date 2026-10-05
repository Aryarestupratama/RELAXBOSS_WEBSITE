<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-005. Hasil Asesmen yang sudah selesai.
 * Isi sensitif terenkripsi (RULE-032): answers, user_context, ai_recommendation, ai_summary.
 * `results` tidak dienkripsi demi agregasi.
 * Akses selalu lewat relasi pemilik (RULE-033): $user->assessmentAttempts().
 */
class AssessmentAttempt extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'assessment_id',
        'answers',
        'results',
        'user_context',
        'ai_recommendation',
        'ai_summary',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'answers' => 'encrypted:array',
            'results' => 'array',
            'user_context' => 'encrypted:array',
            'ai_recommendation' => 'encrypted',
            'ai_summary' => 'encrypted',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Assessment, $this> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
