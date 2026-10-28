<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Jenis notifikasi dalam aplikasi (FR-036, FR-037). Dihitung saat pengguna membuka aplikasi, tidak disimpan
 * (ADR-019). Teks dibuat lembut dan tanpa rasa bersalah (Design bagian 8): jangan menulis "kamu lupa",
 * "menunggumu", atau streak. Kunci nudge juga dipakai sebagai kunci `nudge_dismissals`.
 */
enum NotificationKind: string
{
    case MoodReminder = 'mood_reminder';
    case NudgeAssessment = 'nudge_assessment';
    case NudgeRelaxMate = 'nudge_relaxmate';
    case NudgeArticle = 'nudge_article';
    case Announcement = 'announcement';

    public function message(): string
    {
        return match ($this) {
            self::MoodReminder => 'Sudah catat mood hari ini? Satu menit cukup.',
            self::NudgeAssessment => 'Mau coba Asesmen? Ada beberapa yang bisa dikerjakan sekarang.',
            self::NudgeRelaxMate => 'RelaxMate siap menemani kalau kamu mau bercerita.',
            self::NudgeArticle => 'Ada artikel baru yang mungkin berguna.',
            self::Announcement => 'Ada pembaruan di RelaxBoss.',
        };
    }

    public function actionLabel(): string
    {
        return match ($this) {
            self::MoodReminder => 'Catat mood',
            self::NudgeAssessment => 'Lihat asesmen',
            self::NudgeRelaxMate => 'Buka RelaxMate',
            self::NudgeArticle => 'Baca artikel',
            self::Announcement => '',
        };
    }

    /** Tujuan tautan. `/artikel` adalah halaman Blade sehingga klien memakai tautan native (Architecture bagian 11). */
    public function href(): string
    {
        return match ($this) {
            self::MoodReminder => '/app/mood',
            self::NudgeAssessment => '/app/asesmen',
            self::NudgeRelaxMate => '/app/relaxmate',
            self::NudgeArticle => '/artikel',
            self::Announcement => '/app/notifikasi',
        };
    }

    public function isNative(): bool
    {
        return $this === self::NudgeArticle;
    }

    /** Hanya nudge yang bisa ditutup. Pengingat mood hilang sendiri setelah mood dicatat. */
    public function isDismissible(): bool
    {
        return match ($this) {
            self::NudgeAssessment, self::NudgeRelaxMate, self::NudgeArticle => true,
            default => false,
        };
    }

    /**
     * @return list<string>
     */
    public static function dismissibleValues(): array
    {
        return array_values(array_map(
            static fn (self $kind): string => $kind->value,
            array_filter(self::cases(), static fn (self $kind): bool => $kind->isDismissible()),
        ));
    }
}
