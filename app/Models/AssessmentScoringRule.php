<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SeverityLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-004. Aturan skor per subskala; rentang inklusif setelah pembalikan dan pengali.
 * Tanpa timestamps (Schema).
 */
class AssessmentScoringRule extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'assessment_id',
        'sub_scale',
        'min_score',
        'max_score',
        'interpretation',
        'severity_level',
        'trigger_pfa',
        'pfa_question',
        'static_recommendation',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_score' => 'integer',
            'max_score' => 'integer',
            'severity_level' => SeverityLevel::class,
            'trigger_pfa' => 'boolean',
        ];
    }

    /** @return BelongsTo<Assessment, $this> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
