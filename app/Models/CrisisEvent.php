<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CrisisLayer;
use Illuminate\Database\Eloquent\Model;

/**
 * ENT-009. Satu kejadian krisis untuk hitungan saja (M-5, FR-025).
 * Sengaja tanpa user_id dan tanpa isi; jangan menambah relasi ke pengguna atau pesan.
 */
class CrisisEvent extends Model
{
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = ['layer'];

    /** @var array<string, string> */
    protected $attributes = ['layer' => 'rule'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['layer' => CrisisLayer::class];
    }
}
