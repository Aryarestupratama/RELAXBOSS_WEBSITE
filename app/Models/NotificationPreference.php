<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ENT-013. Pengaturan notifikasi dalam aplikasi milik satu pengguna (FR-036). Tidak dienkripsi karena tidak memuat isi
 * sensitif. Dibuat saat pertama disimpan; tanpa baris dianggap semua mati. Akses selalu lewat relasi pemilik
 * (RULE-033): $user->notificationPreference().
 *
 * `announcements_seen_at` disimpan UTC (RULE-035). `nudge_dismissals` berbentuk {kunci enum: tanggal WIB}.
 */
class NotificationPreference extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'mood_reminder_enabled',
        'mood_reminder_hour',
        'nudges_enabled',
        'announcements_seen_at',
        'nudge_dismissals',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mood_reminder_enabled' => 'boolean',
            'mood_reminder_hour' => 'integer',
            'nudges_enabled' => 'boolean',
            'announcements_seen_at' => 'datetime',
            'nudge_dismissals' => 'array',
        ];
    }

    /** Nilai awal baris baru. Jam bawaan dibaca dari config agar sama dengan yang ditampilkan di Akun (RULE-026). */
    public static function defaults(): array
    {
        return [
            'mood_reminder_enabled' => false,
            'mood_reminder_hour' => (int) config('relaxboss.notifications.default_mood_hour'),
            'nudges_enabled' => false,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
