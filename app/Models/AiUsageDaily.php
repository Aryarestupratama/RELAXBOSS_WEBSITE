<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiFeature;
use Illuminate\Database\Eloquent\Model;

/**
 * ENT-010. Pemakaian AI per hari (UTC), model, dan fitur. Tanpa identitas pengguna.
 * Unik pada (usage_date, model, feature). Diisi oleh `GroqKeyManager` (TASK-016).
 */
class AiUsageDaily extends Model
{
    protected $table = 'ai_usage_daily';

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'usage_date',
        'model',
        'feature',
        'requests',
        'rate_limited',
        'errors',
        'prompt_tokens',
        'completion_tokens',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'feature' => AiFeature::class,
            'requests' => 'integer',
            'rate_limited' => 'integer',
            'errors' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
        ];
    }
}
