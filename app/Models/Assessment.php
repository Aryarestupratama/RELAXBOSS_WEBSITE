<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ENT-002. Instrumen Asesmen. Nonaktif, bukan dihapus, bila sudah punya hasil.
 */
class Assessment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'display_name',
        'description',
        'instructions',
        'estimated_minutes',
        'options',
        'score_multiplier',
        'source_reference',
        'creator_name',
        'creator_institution',
        'validator_name',
        'validator_credential',
        'validated_at',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'array',
            'score_multiplier' => 'decimal:2',
            'validated_at' => 'date',
            'is_active' => 'boolean',
            'estimated_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @param Builder<Assessment> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Asesmen yang boleh tampil di halaman Publik (FR-020, SCR-002): aktif, dan di produksi
     * bukan Instrumen contoh berslug `demo-` (RULE-008, RULE-048).
     *
     * @param  Builder<Assessment>  $query
     */
    public function scopePubliclyListed(Builder $query): void
    {
        $query->where('is_active', true);

        if (app()->isProduction()) {
            $query->where('slug', 'not like', 'demo-%');
        }
    }

    /** Instrumen contoh (dummy) berslug `demo-`. */
    public function isDemo(): bool
    {
        return str_starts_with($this->slug, 'demo-');
    }

    /** Status "tervalidasi" diturunkan dari `validated_at`. */
    public function isValidated(): bool
    {
        return $this->validated_at !== null;
    }

    /** @return HasMany<AssessmentQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('position');
    }

    /** @return HasMany<AssessmentScoringRule, $this> */
    public function scoringRules(): HasMany
    {
        return $this->hasMany(AssessmentScoringRule::class)->orderBy('sub_scale')->orderBy('min_score');
    }

    /** @return HasMany<AssessmentAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
