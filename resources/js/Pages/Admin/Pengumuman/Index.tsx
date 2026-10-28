import { Link } from '@inertiajs/react';
import {
  CalendarClock,
  ChevronLeft,
  ChevronRight,
  CircleCheck,
  Megaphone,
  Pencil,
  PenLine,
  Plus,
  type LucideIcon,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { STATE_LABEL } from '@/lib/adminArticle';

type AnnouncementState = 'draft' | 'scheduled' | 'live';

type AnnouncementRow = {
  id: number;
  title: string;
  state: AnnouncementState;
  published_label: string | null;
  updated_label: string;
};

type IndexProps = {
  announcements: AnnouncementRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
const PILL_BUTTON = 'rounded-full! px-5 text-sm';

const STATE_BADGE: Record<AnnouncementState, { variant: 'mint' | 'sky' | 'secondary'; icon: LucideIcon }> = {
  live: { variant: 'mint', icon: CircleCheck },
  scheduled: { variant: 'sky', icon: CalendarClock },
  draft: { variant: 'secondary', icon: PenLine },
};

function StateBadge({ state }: { state: AnnouncementState }) {
  const { variant, icon: Icon } = STATE_BADGE[state];
  return (
    <Badge variant={variant}>
      <Icon aria-hidden="true" />
      {STATE_LABEL[state]}
    </Badge>
  );
}

function TitleCell({ announcement }: { announcement: AnnouncementRow }) {
  return (
    <span className="flex items-center gap-3">
      <span className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-text" aria-hidden="true">
        <Megaphone className="size-5" />
      </span>
      <span className="min-w-0 font-semibold">{announcement.title}</span>
    </span>
  );
}

function Actions({ announcement }: { announcement: AnnouncementRow }) {
  return (
    <Button asChild variant="outline" className={PILL_BUTTON}>
      <Link href={`/admin/pengumuman/${announcement.id}/ubah`} aria-label={`Ubah ${announcement.title}`}>
        <Pencil className="size-4!" aria-hidden="true" />
        Ubah
      </Link>
    </Button>
  );
}

/** SCR-028: daftar pengumuman. Konten global; tidak ada data pengguna. */
export default function Index({ announcements, prev_page_url, next_page_url }: IndexProps) {
  return (
    <AdminShell title="Pengumuman" staggered>
      <PageHeader
        title="Pengumuman"
        description="Tulis kabar pembaruan website yang dibaca pengguna di lonceng."
        actions={
          <Button asChild className={PILL_BUTTON}>
            <Link href="/admin/pengumuman/baru">
              <Plus aria-hidden="true" />
              Pengumuman baru
            </Link>
          </Button>
        }
      />

      <div className="flex flex-col gap-6">
        <div data-load>
          <InlineNote>
            Pengumuman tidak dihapus (kembalikan ke draf). Lonceng pengguna baru menampilkannya setelah fitur Notifikasi
            dirilis.
          </InlineNote>
        </div>

        {announcements.length === 0 ? (
          <Card tone="sky" data-load className="items-center gap-4 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <Megaphone className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">Belum ada pengumuman. Tulis yang pertama, ya.</p>
            <Button asChild className={PILL_BUTTON}>
              <Link href="/admin/pengumuman/baru">
                <Plus aria-hidden="true" />
                Pengumuman baru
              </Link>
            </Button>
          </Card>
        ) : (
          <section aria-labelledby="daftar-pengumuman" data-load className="flex flex-col gap-3">
            <div className="flex items-center justify-between gap-3">
              <h2 id="daftar-pengumuman" className="text-lg leading-snug font-bold tracking-tight">
                Daftar pengumuman
              </h2>
              <Badge variant="outline">{announcements.length} di halaman ini</Badge>
            </div>

            {/* >= 768px: tabel */}
            <Card className="hidden gap-0 p-3 md:flex">
              <Table className="min-w-[40rem]">
                <TableCaption className="sr-only">Daftar pengumuman</TableCaption>
                <TableHeader>
                  <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                    <TableHead>Pengumuman</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Terbit (WIB)</TableHead>
                    <TableHead>Diubah (WIB)</TableHead>
                    <TableHead>
                      <span className="sr-only">Aksi</span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {announcements.map((announcement) => (
                    <TableRow key={announcement.id}>
                      <TableHead scope="row" className={ROW_HEAD}>
                        <TitleCell announcement={announcement} />
                      </TableHead>
                      <TableCell>
                        <StateBadge state={announcement.state} />
                      </TableCell>
                      <TableCell className="whitespace-nowrap text-text-secondary">
                        {announcement.published_label ?? '-'}
                      </TableCell>
                      <TableCell className="whitespace-nowrap text-text-secondary">{announcement.updated_label}</TableCell>
                      <TableCell className="text-right">
                        <Actions announcement={announcement} />
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </Card>

            {/* < 768px: kartu */}
            <ul className="grid gap-3 md:hidden">
              {announcements.map((announcement) => (
                <li key={announcement.id}>
                  <Card className="gap-3 p-4">
                    <TitleCell announcement={announcement} />
                    <StateBadge state={announcement.state} />
                    <p className="text-sm text-text-secondary">
                      Terbit: {announcement.published_label ?? '-'} · Diubah: {announcement.updated_label}
                    </p>
                    <div className="flex">
                      <Actions announcement={announcement} />
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          </section>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman pengumuman" data-load className="flex justify-between gap-3">
            {prev_page_url ? (
              <Button asChild variant="outline" className={PILL_BUTTON}>
                <Link href={prev_page_url}>
                  <ChevronLeft aria-hidden="true" />
                  Sebelumnya
                </Link>
              </Button>
            ) : (
              <span />
            )}
            {next_page_url ? (
              <Button asChild variant="outline" className={PILL_BUTTON}>
                <Link href={next_page_url}>
                  Berikutnya
                  <ChevronRight aria-hidden="true" />
                </Link>
              </Button>
            ) : null}
          </nav>
        ) : null}
      </div>
    </AdminShell>
  );
}
