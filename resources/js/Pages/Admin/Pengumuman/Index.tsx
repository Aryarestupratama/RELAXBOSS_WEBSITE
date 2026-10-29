import { Link } from '@inertiajs/react';
import {
  CalendarClock,
  CircleCheck,
  Megaphone,
  Pencil,
  PenLine,
  Plus,
  type LucideIcon,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import DataTable, { type DataTableColumn } from '@/Components/shared/DataTable';
import DataTableCard from '@/Components/shared/DataTableCard';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { STATE_LABEL } from '@/lib/adminArticle';
import { ROW_BUTTON } from '@/lib/adminTable';

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
      <span className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-text md:size-9" aria-hidden="true">
        <Megaphone className="size-5" />
      </span>
      <span className="min-w-0 font-semibold">{announcement.title}</span>
    </span>
  );
}

function Actions({ announcement }: { announcement: AnnouncementRow }) {
  return (
    <Button asChild variant="outline" className={ROW_BUTTON}>
      <Link href={`/admin/pengumuman/${announcement.id}/ubah`} aria-label={`Ubah ${announcement.title}`}>
        <Pencil className="size-4!" aria-hidden="true" />
        Ubah
      </Link>
    </Button>
  );
}

const COLUMNS: DataTableColumn<AnnouncementRow>[] = [
  { id: 'title', header: 'Pengumuman', rowHeader: true, cell: (announcement) => <TitleCell announcement={announcement} /> },
  { id: 'state', header: 'Status', cell: (announcement) => <StateBadge state={announcement.state} /> },
  {
    id: 'published',
    header: 'Terbit (WIB)',
    className: 'whitespace-nowrap text-text-secondary',
    cell: (announcement) => announcement.published_label ?? '-',
  },
  {
    id: 'updated',
    header: 'Diubah (WIB)',
    className: 'whitespace-nowrap text-text-secondary',
    cell: (announcement) => announcement.updated_label,
  },
  { id: 'actions', header: 'Aksi', hideHeader: true, align: 'right', cell: (announcement) => <Actions announcement={announcement} /> },
];

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

        <DataTableCard
          id="daftar-pengumuman"
          title="Daftar pengumuman"
          countLabel={announcements.length > 0 ? `${announcements.length} di halaman ini` : undefined}
          isEmpty={announcements.length === 0}
          empty={{
            icon: Megaphone,
            message: 'Belum ada pengumuman. Tulis yang pertama, ya.',
            action: (
              <Button asChild className={PILL_BUTTON}>
                <Link href="/admin/pengumuman/baru">
                  <Plus aria-hidden="true" />
                  Pengumuman baru
                </Link>
              </Button>
            ),
          }}
          pager={{ label: 'Halaman pengumuman', prevUrl: prev_page_url, nextUrl: next_page_url }}
        >
          <DataTable
            caption="Daftar pengumuman"
            columns={COLUMNS}
            rows={announcements}
            getRowKey={(announcement) => announcement.id}
            mobileCard={(announcement) => (
              <>
                <TitleCell announcement={announcement} />
                <StateBadge state={announcement.state} />
                <p className="text-sm text-text-secondary">
                  Terbit: {announcement.published_label ?? '-'} · Diubah: {announcement.updated_label}
                </p>
                <div className="flex">
                  <Actions announcement={announcement} />
                </div>
              </>
            )}
          />
        </DataTableCard>
      </div>
    </AdminShell>
  );
}
