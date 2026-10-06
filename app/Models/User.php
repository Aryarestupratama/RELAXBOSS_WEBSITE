<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TrainingConsentChoice;
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
            'ai_training_consent_choice' => TrainingConsentChoice::class,
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

    /** Consent AI versi terbaru sudah disetujui (FR-013). Versi lama dianggap belum. */
    public function hasAiConsent(): bool
    {
        return $this->ai_consent_at !== null
            && (int) $this->ai_consent_version >= (int) config('relaxboss.ai_consent_version');
    }

    /** Pilihan persetujuan pelatihan sudah dibuat untuk teks versi terbaru (ya atau tidak sama-sama dihitung). */
    public function hasTrainingChoice(): bool
    {
        return $this->ai_training_consent_choice !== null
            && (int) $this->ai_training_consent_version >= (int) config('relaxboss.ai_training_consent_version');
    }

    /** Syarat membuka fitur AI: consent AI dan pilihan pelatihan (`EnsureAiConsent`). */
    public function hasCompletedAiConsent(): bool
    {
        return $this->hasAiConsent() && $this->hasTrainingChoice();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
