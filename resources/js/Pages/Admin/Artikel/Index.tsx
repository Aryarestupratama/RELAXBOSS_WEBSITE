import { Link } from '@inertiajs/react';
import {
  CalendarClock,
  CircleCheck,
  ExternalLink,
  FileText,
  FlaskConical,
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

type ArticleState = 'draft' | 'scheduled' | 'live';

type ArticleRow = {
  id: number;
  slug: string;
  title: string;
  category_label: string;
  state: ArticleState;
  is_demo: boolean;
  published_label: string | null;
  updated_label: string;
  public_url: string | null;
};

type IndexProps = {
  articles: ArticleRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const PILL_BUTTON = 'rounded-full! px-5 text-sm';

const STATE_BADGE: Record<ArticleState, { variant: 'mint' | 'sky' | 'secondary'; icon: LucideIcon }> = {
  live: { variant: 'mint', icon: CircleCheck },
  scheduled: { variant: 'sky', icon: CalendarClock },
  draft: { variant: 'secondary', icon: PenLine },
};

function StateBadges({ article }: { article: ArticleRow }) {
  const { variant, icon: Icon } = STATE_BADGE[article.state];
  return (
    <span className="flex flex-wrap gap-1.5">
      <Badge variant={variant}>
        <Icon aria-hidden="true" />
        {STATE_LABEL[article.state]}
      </Badge>
      {article.is_demo ? (
        <Badge variant="peach">
          <FlaskConical aria-hidden="true" />
          Contoh
        </Badge>
      ) : null}
    </span>
  );
}

function TitleCell({ article }: { article: ArticleRow }) {
  return (
    <span className="flex items-center gap-3">
      <span
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-sky text-text md:size-9"
        aria-hidden="true"
      >
        <FileText className="size-5" />
      </span>
      <span className="min-w-0">
        <span className="block font-semibold">{article.title}</span>
        <span className="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-text-secondary">
          <Badge variant="outline">{article.category_label}</Badge>
          {article.slug}
        </span>
      </span>
    </span>
  );
}

function Actions({ article }: { article: ArticleRow }) {
  return (
    <span className="flex flex-wrap justify-end gap-2">
      {article.public_url ? (
        <Button asChild variant="ghost" className={ROW_BUTTON}>
          <a
            href={article.public_url}
            target="_blank"
            rel="noopener"
            aria-label={`Lihat ${article.title} di halaman Publik (tab baru)`}
          >
            <ExternalLink className="size-4!" aria-hidden="true" />
            Lihat
          </a>
        </Button>
      ) : null}
      <Button asChild variant="outline" className={ROW_BUTTON}>
        <Link href={`/admin/artikel/${article.id}/ubah`} aria-label={`Ubah ${article.title}`}>
          <Pencil className="size-4!" aria-hidden="true" />
          Ubah
        </Link>
      </Button>
    </span>
  );
}

const COLUMNS: DataTableColumn<ArticleRow>[] = [
  { id: 'title', header: 'Artikel', rowHeader: true, cell: (article) => <TitleCell article={article} /> },
  { id: 'state', header: 'Status', cell: (article) => <StateBadges article={article} /> },
  {
    id: 'published',
    header: 'Terbit (WIB)',
    className: 'whitespace-nowrap text-text-secondary',
    cell: (article) => article.published_label ?? '-',
  },
  { id: 'updated', header: 'Diubah (WIB)', className: 'whitespace-nowrap text-text-secondary', cell: (article) => article.updated_label },
  { id: 'actions', header: 'Aksi', hideHeader: true, align: 'right', cell: (article) => <Actions article={article} /> },
];

/** SCR-026: daftar artikel. Konten publik; tidak ada data pengguna. */
export default function Index({ articles, prev_page_url, next_page_url }: IndexProps) {
  return (
    <AdminShell title="Artikel" staggered>
      <PageHeader
        title="Artikel"
        description="Tulis dan terbitkan artikel edukasi untuk halaman Publik."
        actions={
          <Button asChild className={PILL_BUTTON}>
            <Link href="/admin/artikel/baru">
              <Plus aria-hidden="true" />
              Artikel baru
            </Link>
          </Button>
        }
      />

      <div className="flex flex-col gap-6">
        <div data-load>
          <InlineNote>Artikel tidak dihapus (kembalikan ke draf), dan sebelum terbit pastikan tidak ada klaim diagnosis, UMB, atau validasi psikolog yang belum terkonfirmasi.</InlineNote>
        </div>

        <DataTableCard
          id="daftar-artikel"
          title="Daftar artikel"
          countLabel={articles.length > 0 ? `${articles.length} di halaman ini` : undefined}
          isEmpty={articles.length === 0}
          empty={{
            icon: FileText,
            message: 'Belum ada artikel. Tulis yang pertama, ya.',
            action: (
              <Button asChild className={PILL_BUTTON}>
                <Link href="/admin/artikel/baru">
                  <Plus aria-hidden="true" />
                  Artikel baru
                </Link>
              </Button>
            ),
          }}
          pager={{ label: 'Halaman artikel', prevUrl: prev_page_url, nextUrl: next_page_url }}
        >
          <DataTable
            caption="Daftar artikel"
            columns={COLUMNS}
            rows={articles}
            getRowKey={(article) => article.id}
            minWidthClass="min-w-[44rem]"
            mobileCard={(article) => (
              <>
                <TitleCell article={article} />
                <StateBadges article={article} />
                <p className="text-sm text-text-secondary">
                  Terbit: {article.published_label ?? '-'} · Diubah: {article.updated_label}
                </p>
                <div className="flex">
                  <Actions article={article} />
                </div>
              </>
            )}
          />
        </DataTableCard>
      </div>
    </AdminShell>
  );
}
