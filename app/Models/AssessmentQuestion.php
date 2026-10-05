<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-003. Butir pertanyaan; tanpa timestamps (Schema).
 */
class AssessmentQuestion extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'assessment_id',
        'position',
        'text',
        'sub_scale',
        'is_reversed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_reversed' => 'boolean',
        ];
    }

    /** @return BelongsTo<Assessment, $this> */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
