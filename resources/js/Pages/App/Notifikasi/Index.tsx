import { useEffect, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Bell, BookOpen, CircleAlert, ClipboardCheck, Megaphone, MessageCircle, Smile, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import EmptyState from '@/Components/shared/EmptyState';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import {
  ANNOUNCEMENT_PROSE_CLASS,
  type ActionNotification,
  type AnnouncementNotification,
  type NotificationItem,
} from '@/lib/notification';

type IndexProps = {
  items: NotificationItem[];
};

const ICONS: Record<ActionNotification['kind'], LucideIcon> = {
  mood_reminder: Smile,
  nudge_assessment: ClipboardCheck,
  nudge_relaxmate: MessageCircle,
  nudge_article: BookOpen,
};

const PILL = 'rounded-full! px-5 text-sm';

function isAnnouncement(item: NotificationItem): item is AnnouncementNotification {
  return item.kind === 'announcement';
}

/** SCR-027. Dihitung saat dibuka (ADR-019); pengingat dan ajakan lebih dulu, lalu pengumuman. */
export default function Index({ items }: IndexProps) {
  const actions = items.filter((item): item is ActionNotification => !isAnnouncement(item));
  const announcements = items.filter(isAnnouncement);

  // Penanda "Baru" diambil saat halaman dibuka; setelah ditandai dibaca di server, kartu tetap menampilkannya
  // selama halaman ini terbuka agar pengguna tidak kehilangan petunjuk.
  const [freshKeys] = useState(() => new Set(announcements.filter((item) => item.unread).map((item) => item.key)));
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState('');

  useEffect(() => {
    if (freshKeys.size === 0) {
      return;
    }
    router.post('/app/notifikasi/dibaca', {}, { preserveState: true, preserveScroll: true, replace: true });
  }, [freshKeys]);

  const dismiss = (item: ActionNotification) => {
    router.post(
      '/app/notifikasi/tutup',
      { kind: item.kind },
      {
        preserveState: true,
        preserveScroll: true,
        onStart: () => {
          setError(null);
          setNotice('');
        },
        onSuccess: () => setNotice('Notifikasi ditutup untuk hari ini.'),
        onError: () => setError('Belum bisa menutup notifikasi ini. Coba lagi sebentar lagi.'),
      },
    );
  };

  return (
    <AppShell title="Notifikasi" staggered>
      <div className="mx-auto w-full max-w-2xl min-w-0">
        <PageHeader title="Notifikasi" description="Pengingat lembut dan kabar terbaru dari RelaxBoss." />

        <p role="status" className="sr-only">
          {notice}
        </p>
        {error ? (
          <Alert variant="destructive" className="mb-4">
            <CircleAlert aria-hidden="true" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        ) : null}

        {items.length === 0 ? (
          <Card data-load className="p-6">
            <EmptyState
              icon={Bell}
              message="Belum ada yang perlu dibaca."
              action={
                <Button asChild variant="outline" className={PILL}>
                  <Link href="/dashboard">Ke Dashboard</Link>
                </Button>
              }
            />
          </Card>
        ) : (
          <div className="space-y-6">
            {actions.length > 0 ? (
              <section aria-labelledby="judul-untukmu" className="space-y-3">
                <h2 id="judul-untukmu" data-load className="text-sm font-semibold text-text-secondary">
                  Untukmu hari ini
                </h2>
                <ul className="space-y-3">
                  {actions.map((item) => {
                    const Icon = ICONS[item.kind];
                    return (
                      <li key={item.key} data-load>
                        <Card className="p-4 sm:p-5">
                          <div className="flex items-start gap-3.5">
                            <span
                              aria-hidden="true"
                              className="grid size-11 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary"
                            >
                              <Icon className="size-5" />
                            </span>
                            <div className="min-w-0 flex-1">
                              <p className="text-sm leading-relaxed font-medium">{item.text}</p>
                              <div className="mt-3 flex flex-wrap items-center gap-2">
                                <Button asChild variant="outline" className={PILL}>
                                  {item.native ? (
                                    <a href={item.href}>{item.action_label}</a>
                                  ) : (
                                    <Link href={item.href}>{item.action_label}</Link>
                                  )}
                                </Button>
                                {item.dismissible ? (
                                  <Button type="button" variant="ghost" className={PILL} onClick={() => dismiss(item)}>
                                    Tutup
                                  </Button>
                                ) : null}
                              </div>
                            </div>
                          </div>
                        </Card>
                      </li>
                    );
                  })}
                </ul>
              </section>
            ) : null}

            {announcements.length > 0 ? (
              <section aria-labelledby="judul-pembaruan" className="space-y-3">
                <h2 id="judul-pembaruan" data-load className="text-sm font-semibold text-text-secondary">
                  Pembaruan RelaxBoss
                </h2>
                <ul className="space-y-3">
                  {announcements.map((item) => (
                    <li key={item.key} data-load>
                      <Card className="gap-3 p-4 sm:p-5">
                        <div className="flex items-start gap-3.5">
                          <span
                            aria-hidden="true"
                            className="grid size-11 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary"
                          >
                            <Megaphone className="size-5" />
                          </span>
                          <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-2">
                              <h3 className="text-base leading-snug font-semibold">{item.title}</h3>
                              {freshKeys.has(item.key) ? <Badge variant="lavender">Baru</Badge> : null}
                            </div>
                            {item.date_label ? <p className="mt-0.5 text-xs text-text-secondary">{item.date_label}</p> : null}
                          </div>
                        </div>
                        <div className={ANNOUNCEMENT_PROSE_CLASS} dangerouslySetInnerHTML={{ __html: item.html }} />
                      </Card>
                    </li>
                  ))}
                </ul>
              </section>
            ) : null}
          </div>
        )}

        <p className="mt-6 text-xs text-text-secondary">
          Notifikasi hanya muncul di sini saat kamu membuka RelaxBoss, tanpa email.{' '}
          <Link href="/app/akun" className="font-medium text-primary underline underline-offset-4 hover:text-nav-hover">
            Atur di Akun
          </Link>
          .
        </p>
      </div>
    </AppShell>
  );
}
