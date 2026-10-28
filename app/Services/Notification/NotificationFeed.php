<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationKind;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Services\Article\ArticleRenderer;
use App\Support\Wib;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * ADR-019: notifikasi dihitung saat pengguna membuka aplikasi, bukan dikirim. Tanpa tabel notifikasi, tanpa command,
 * tanpa cron, tanpa pemanggilan AI (RULE-034). Isinya generik: tidak memuat skor, hasil, atau isi catatan (FR-036).
 *
 * Semua query data pengguna lewat relasi pemilik (RULE-033). Mood Entry hanya dicek ada atau tidaknya hari ini (WIB);
 * `note` tidak pernah dibaca. Hasil Asesmen hanya dibaca `completed_at` terbarunya.
 */
final class NotificationFeed
{
    public function __construct(private readonly ArticleRenderer $renderer) {}

    /**
     * Hitungan untuk lonceng (prop bersama di setiap halaman): pengumuman belum dibaca ditambah pengingat mood yang
     * jatuh tempo. Nudge sengaja tidak dihitung agar lonceng tidak terasa menekan. Ringan: satu baris preferensi
     * (di-cache pada instance pengguna), satu `count`, dan satu `exists` bila pengingat mood aktif dan sudah lewat jamnya.
     */
    public function unreadCount(User $user): int
    {
        $preference = $this->preference($user);

        $count = $this->unreadAnnouncements($preference)->count();

        if ($this->moodReminderDue($user, $preference)) {
            $count++;
        }

        return $count;
    }

    /**
     * Isi halaman Notifikasi (SCR-027): pengingat dan nudge lebih dulu, lalu pengumuman terbaru.
     *
     * @return list<array<string, mixed>>
     */
    public function for(User $user): array
    {
        $preference = $this->preference($user);
        $items = [];

        if ($this->moodReminderDue($user, $preference)) {
            $items[] = $this->action(NotificationKind::MoodReminder);
        }

        if ($preference !== null && $preference->nudges_enabled) {
            foreach ($this->nudges($user, $preference) as $kind) {
                $items[] = $this->action($kind);
            }
        }

        $seenAt = $preference?->announcements_seen_at;

        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit((int) config('relaxboss.notifications.max_visible'))
            ->get();

        foreach ($announcements as $announcement) {
            $items[] = [
                'key' => 'announcement-'.$announcement->id,
                'kind' => NotificationKind::Announcement->value,
                'title' => $announcement->title,
                'html' => $this->renderer->html($announcement->body_markdown),
                'date_label' => $announcement->published_at === null
                    ? null
                    : Wib::toWib($announcement->published_at)->locale('id')->translatedFormat('j M Y'),
                'unread' => $seenAt === null || ($announcement->published_at !== null && $announcement->published_at->gt($seenAt)),
            ];
        }

        return $items;
    }

    /**
     * Preferensi pengguna, dimuat sekali per permintaan lewat relasi pemilik. `null` berarti belum pernah disimpan.
     */
    public function preference(User $user): ?NotificationPreference
    {
        return $user->loadMissing('notificationPreference')->notificationPreference;
    }

    /**
     * @return Builder<Announcement>
     */
    private function unreadAnnouncements(?NotificationPreference $preference): Builder
    {
        $seenAt = $preference?->announcements_seen_at;

        return Announcement::query()
            ->published()
            ->when($seenAt !== null, static fn ($query) => $query->where('published_at', '>', $seenAt));
    }

    /** Pengingat aktif, jam WIB yang dipilih sudah lewat, dan belum ada Mood Entry hari ini (WIB). */
    private function moodReminderDue(User $user, ?NotificationPreference $preference): bool
    {
        if ($preference === null || ! $preference->mood_reminder_enabled) {
            return false;
        }

        $now = Wib::now();

        if ($now->hour < $preference->mood_reminder_hour) {
            return false;
        }

        // Rentang, bukan `=`: di SQLite `entry_date` tersimpan sebagai "Y-m-d 00:00:00" (cast `date`).
        return ! $user->moodEntries()
            ->where('entry_date', '>=', $now->format('Y-m-d'))
            ->where('entry_date', '<', $now->addDay()->format('Y-m-d'))
            ->exists();
    }

    /**
     * Nudge yang layak tampil hari ini, urut: artikel, Asesmen, RelaxMate.
     *
     * @return list<NotificationKind>
     */
    private function nudges(User $user, NotificationPreference $preference): array
    {
        $today = Wib::date();
        $kinds = [];

        // Artikel: ada yang terbit dalam jendela terakhir. Setelah ditutup, tidak muncul lagi sampai ada artikel yang
        // lebih baru dari hari penutupan (tanpa kolom `articles_seen_at`; OQ-16).
        $newest = Article::query()
            ->published()
            ->where('published_at', '>=', now()->subDays((int) config('relaxboss.notifications.article_recent_days')))
            ->max('published_at');

        if ($newest !== null) {
            $dismissed = $this->dismissedOn($preference, NotificationKind::NudgeArticle);
            $newestDate = Wib::date(CarbonImmutable::parse((string) $newest, 'UTC'));

            if ($dismissed === null || $dismissed < $newestDate) {
                $kinds[] = NotificationKind::NudgeArticle;
            }
        }

        if ($this->assessmentIdle($user) && $this->dismissedOn($preference, NotificationKind::NudgeAssessment) !== $today) {
            $kinds[] = NotificationKind::NudgeAssessment;
        }

        // RelaxMate hanya untuk yang belum pernah punya Percakapan.
        if (
            $this->dismissedOn($preference, NotificationKind::NudgeRelaxMate) !== $today
            && ! $user->conversations()->exists()
        ) {
            $kinds[] = NotificationKind::NudgeRelaxMate;
        }

        return $kinds;
    }

    /** Ada Asesmen yang bisa dikerjakan, dan pengguna belum pernah mengerjakan atau terakhir lebih dari N hari lalu. */
    private function assessmentIdle(User $user): bool
    {
        if (! Assessment::query()->publiclyListed()->exists()) {
            return false;
        }

        $last = $user->assessmentAttempts()->max('completed_at');

        if ($last === null) {
            return true;
        }

        $threshold = now()->subDays((int) config('relaxboss.notifications.assessment_idle_days'));

        return CarbonImmutable::parse((string) $last, 'UTC')->lt($threshold);
    }

    private function dismissedOn(NotificationPreference $preference, NotificationKind $kind): ?string
    {
        $value = ($preference->nudge_dismissals ?? [])[$kind->value] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function action(NotificationKind $kind): array
    {
        return [
            'key' => $kind->value,
            'kind' => $kind->value,
            'text' => $kind->message(),
            'action_label' => $kind->actionLabel(),
            'href' => $kind->href(),
            'native' => $kind->isNative(),
            'dismissible' => $kind->isDismissible(),
        ];
    }
}
