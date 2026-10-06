<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ENT-011. Satu baris per pembukaan isi oleh admin (FR-031).
 * Tidak berisi isi pesan atau identitas pemilik data. Hanya ditulis, tidak pernah diubah.
 */
class AdminAccessLog extends Model
{
    public const RESOURCE_CONVERSATION = 'conversation';

    public const RESOURCE_ATTEMPT = 'assessment_attempt';

    /** Tabel hanya punya created_at. */
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'admin_id',
        'resource_type',
        'resource_id',
    ];
}
