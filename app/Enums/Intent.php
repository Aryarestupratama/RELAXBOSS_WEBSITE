<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * 17 kode maksud pesan pengguna (ADR-017, Architecture 6.4). Intent adalah enum, bukan tabel.
 * Intent yang pernah dipakai tidak dihapus dari enum, hanya ditandai usang.
 */
enum Intent: string
{
    // Prioritas 1: darurat
    case CrisisSuicide = 'crisis_suicide';
    case CrisisSelfharm = 'crisis_selfharm';

    // Prioritas 2: tinggi
    case AnxietyPanic = 'anxiety_panic';
    case DepressionSadness = 'depression_sadness';
    case GriefLoss = 'grief_loss';
    case TraumaAbuse = 'trauma_abuse';

    // Prioritas 3: normal
    case RelationshipConflict = 'relationship_conflict';
    case AcademicPressure = 'academic_pressure';
    case LowSelfesteem = 'low_selfesteem';
    case VentingStress = 'venting_stress';
    case InsomniaSleep = 'insomnia_sleep';
    case BurnoutExhaustion = 'burnout_exhaustion';
    case LonelinessIsolation = 'loneliness_isolation';
    case AngerFrustration = 'anger_frustration';
    case SelfDevelopment = 'self_development';

    // Prioritas 4: rendah
    case GreetingCasual = 'greeting_casual';
    case OutOfScope = 'out_of_scope';

    /** 1 = darurat, 2 = tinggi, 3 = normal, 4 = rendah. */
    public function priority(): int
    {
        return match ($this) {
            self::CrisisSuicide, self::CrisisSelfharm => 1,
            self::AnxietyPanic, self::DepressionSadness, self::GriefLoss, self::TraumaAbuse => 2,
            self::GreetingCasual, self::OutOfScope => 4,
            default => 3,
        };
    }

    /** Nama singkat berbahasa Indonesia untuk tinjauan admin (SCR-022). */
    public function label(): string
    {
        return match ($this) {
            self::CrisisSuicide => 'Krisis: pikiran bunuh diri',
            self::CrisisSelfharm => 'Krisis: menyakiti diri',
            self::AnxietyPanic => 'Cemas atau panik',
            self::DepressionSadness => 'Sedih berkepanjangan',
            self::GriefLoss => 'Duka dan kehilangan',
            self::TraumaAbuse => 'Trauma atau kekerasan',
            self::RelationshipConflict => 'Konflik hubungan',
            self::AcademicPressure => 'Tekanan akademik',
            self::LowSelfesteem => 'Harga diri rendah',
            self::VentingStress => 'Mengeluarkan stres',
            self::InsomniaSleep => 'Sulit tidur',
            self::BurnoutExhaustion => 'Kelelahan (burnout)',
            self::LonelinessIsolation => 'Kesepian',
            self::AngerFrustration => 'Marah atau frustrasi',
            self::SelfDevelopment => 'Pengembangan diri',
            self::GreetingCasual => 'Sapaan santai',
            self::OutOfScope => 'Di luar cakupan',
        };
    }

    public function isCrisis(): bool
    {
        return $this->priority() === 1;
    }

    /**
     * Semua kode, untuk disisipkan ke prompt (satu sumber kebenaran).
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
