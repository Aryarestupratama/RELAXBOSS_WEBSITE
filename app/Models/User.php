<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Hanya kolom profil yang boleh diisi massal.
     * role, is_active, dan kolom consent diatur eksplisit di kode (bukan dari input).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_name',
        'major',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'ai_consent_at' => 'datetime',
            'ai_consent_version' => 'integer',
            'ai_training_consent_at' => 'datetime',
            'ai_training_consent_version' => 'integer',
        ];
    }

    /** @return HasMany<AssessmentAttempt, $this> */
    public function assessmentAttempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** @return HasMany<MoodEntry, $this> */
    public function moodEntries(): HasMany
    {
        return $this->hasMany(MoodEntry::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
