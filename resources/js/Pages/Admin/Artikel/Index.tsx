import { Link } from '@inertiajs/react';
import {
  CalendarClock,
  ChevronLeft,
  ChevronRight,
  CircleCheck,
  ExternalLink,
  FileText,
  FlaskConical,
  Info,
  Pencil,
  PenLine,
  Plus,
  CheckCircle,
  type LucideIcon,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { STATE_LABEL, STATUS_TEXT } from '@/lib/adminArticle';

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
  status: string | null;
};

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
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
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-sky text-text"
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
        <Button asChild variant="ghost" className={PILL_BUTTON}>
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
      <Button asChild variant="outline" className={PILL_BUTTON}>
        <Link href={`/admin/artikel/${article.id}/ubah`} aria-label={`Ubah ${article.title}`}>
          <Pencil className="size-4!" aria-hidden="true" />
          Ubah
        </Link>
      </Button>
    </span>
  );
}

/** SCR-026: daftar artikel. Konten publik; tidak ada data pengguna. */
export default function Index({ articles, prev_page_url, next_page_url, status }: IndexProps) {
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
        {status && STATUS_TEXT[status] ? (
          <div data-load>
            <Alert className="rounded-2xl border-transparent border-l-4 border-l-success px-4 py-3 shadow-card">
              <CheckCircle className="text-success!" aria-hidden="true" />
              <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">
                {STATUS_TEXT[status]}
              </AlertDescription>
            </Alert>
          </div>
        ) : null}

        <div data-load>
          <Alert role="note" className="rounded-2xl border-transparent border-l-4 border-l-brand-strong px-4 py-3 shadow-card">
            <Info className="text-brand-strong!" aria-hidden="true" />
            <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">
              Artikel tidak dihapus; kembalikan ke draf bila tidak ingin tampil. Sebelum terbit, pastikan tidak ada
              klaim diagnosis, kolaborasi UMB, atau validasi psikolog yang belum terkonfirmasi.
            </AlertDescription>
          </Alert>
        </div>

        {articles.length === 0 ? (
          <Card tone="sky" data-load className="items-center gap-4 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <FileText className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">Belum ada artikel. Tulis yang pertama, ya.</p>
            <Button asChild className={PILL_BUTTON}>
              <Link href="/admin/artikel/baru">
                <Plus aria-hidden="true" />
                Artikel baru
              </Link>
            </Button>
          </Card>
        ) : (
          <section aria-labelledby="daftar-artikel" data-load className="flex flex-col gap-3">
            <div className="flex items-center justify-between gap-3">
              <h2 id="daftar-artikel" className="text-lg leading-snug font-bold tracking-tight">
                Daftar artikel
              </h2>
              <Badge variant="outline">{articles.length} di halaman ini</Badge>
            </div>

            {/* >= 768px: tabel */}
            <Card className="hidden gap-0 p-3 md:flex">
              <Table className="min-w-[44rem]">
                <TableCaption className="sr-only">Daftar artikel</TableCaption>
                <TableHeader>
                  <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                    <TableHead>Artikel</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Terbit (WIB)</TableHead>
                    <TableHead>Diubah (WIB)</TableHead>
                    <TableHead>
                      <span className="sr-only">Aksi</span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {articles.map((article) => (
                    <TableRow key={article.id}>
                      <TableHead scope="row" className={ROW_HEAD}>
                        <TitleCell article={article} />
                      </TableHead>
                      <TableCell>
                        <StateBadges article={article} />
                      </TableCell>
                      <TableCell className="whitespace-nowrap text-text-secondary">
                        {article.published_label ?? '-'}
                      </TableCell>
                      <TableCell className="whitespace-nowrap text-text-secondary">{article.updated_label}</TableCell>
                      <TableCell className="text-right">
                        <Actions article={article} />
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </Card>

            {/* < 768px: kartu */}
            <ul className="grid gap-3 md:hidden">
              {articles.map((article) => (
                <li key={article.id}>
                  <Card className="gap-3 p-4">
                    <TitleCell article={article} />
                    <StateBadges article={article} />
                    <p className="text-sm text-text-secondary">
                      Terbit: {article.published_label ?? '-'} · Diubah: {article.updated_label}
                    </p>
                    <div className="flex">
                      <Actions article={article} />
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          </section>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman artikel" data-load className="flex justify-between gap-3">
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
