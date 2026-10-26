import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CircleCheck, CircleDashed, ClipboardList } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import MonitoringFilter from '@/Components/shared/MonitoringFilter';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringPager from '@/Components/shared/MonitoringPager';
import MonitoringPseudoId from '@/Components/shared/MonitoringPseudoId';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { formatDate, type AttemptFilter, type AttemptRow } from '@/lib/adminMonitoring';

type AsesmenIndexProps = {
  filter: AttemptFilter;
  rows: AttemptRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const FILTERS: { key: AttemptFilter; label: string }[] = [
  { key: 'ai', label: 'Dengan rekomendasi AI' },
  { key: 'semua', label: 'Semua hasil' },
];

const EMPTY: Record<AttemptFilter, string> = {
  ai: 'Belum ada rekomendasi AI untuk ditinjau.',
  semua: 'Belum ada hasil Asesmen.',
};

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
const PILL_BUTTON = 'rounded-full! px-5 text-sm';

function AiBadge({ row }: { row: AttemptRow }) {
  return row.has_ai_recommendation ? (
    <Badge variant="mint">
      <CircleCheck aria-hidden="true" />
      Ada
    </Badge>
  ) : (
    <Badge variant="secondary">
      <CircleDashed aria-hidden="true" />
      Belum ada
    </Badge>
  );
}

function OpenButton({ row }: { row: AttemptRow }) {
  return (
    <Button asChild variant="outline" className={PILL_BUTTON}>
      <Link href={`/admin/monitoring/asesmen/${row.id}`} aria-label={`Buka hasil ${row.pseudo_id}`}>
        Buka
        <ArrowRight aria-hidden="true" />
      </Link>
    </Button>
  );
}

/** SCR-022, tab Asesmen: daftar hasil tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function AsesmenIndex({ filter, rows, prev_page_url, next_page_url }: AsesmenIndexProps) {
  return (
    <AdminShell title="Monitoring Asesmen" staggered>
      <MonitoringHeader current="asesmen" />

      <div className="flex flex-col gap-6">
        <MonitoringFilter label="Saringan hasil Asesmen" basePath="/admin/monitoring/asesmen" options={FILTERS} current={filter} />

        {rows.length === 0 ? (
          <Card tone="lavender" data-load className="items-center gap-3 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <ClipboardList className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">{EMPTY[filter]}</p>
          </Card>
        ) : (
          <section aria-labelledby="daftar-hasil" data-load className="flex flex-col gap-3">
            <div className="flex items-center justify-between gap-3">
              <h2 id="daftar-hasil" className="text-lg leading-snug font-bold tracking-tight">
                Hasil untuk ditinjau
              </h2>
              <Badge variant="outline">{rows.length} di halaman ini</Badge>
            </div>

            {/* >= 768px: tabel */}
            <Card className="hidden gap-0 p-3 md:flex">
              <Table className="min-w-[36rem]">
                <TableCaption className="sr-only">Daftar hasil Asesmen untuk ditinjau</TableCaption>
                <TableHeader>
                  <TableRow className="border-0 bg-neutral-soft hover:bg-neutral-soft [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                    <TableHead>ID semu</TableHead>
                    <TableHead>Tanggal</TableHead>
                    <TableHead>Asesmen</TableHead>
                    <TableHead>Rekomendasi AI</TableHead>
                    <TableHead>
                      <span className="sr-only">Aksi</span>
                    </TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {rows.map((row) => (
                    <TableRow key={row.id}>
                      <TableHead scope="row" className={ROW_HEAD}>
                        <MonitoringPseudoId value={row.pseudo_id} />
                      </TableHead>
                      <TableCell className="whitespace-nowrap text-text-secondary">{formatDate(row.date)}</TableCell>
                      <TableCell className="font-medium">{row.assessment}</TableCell>
                      <TableCell>
                        <AiBadge row={row} />
                      </TableCell>
                      <TableCell className="text-right">
                        <OpenButton row={row} />
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </Card>

            {/* < 768px: kartu */}
            <ul className="grid gap-3 md:hidden">
              {rows.map((row) => (
                <li key={row.id}>
                  <Card className="gap-3 p-4">
                    <MonitoringPseudoId value={row.pseudo_id} />
                    <p className="font-medium">{row.assessment}</p>
                    <p className="inline-flex items-center gap-1.5 text-sm text-text-secondary">
                      <CalendarDays className="size-4" aria-hidden="true" />
                      {formatDate(row.date)}
                    </p>
                    <span className="flex flex-wrap gap-1.5">
                      <AiBadge row={row} />
                    </span>
                    <div>
                      <OpenButton row={row} />
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          </section>
        )}

        <MonitoringPager label="Halaman hasil Asesmen" prevUrl={prev_page_url} nextUrl={next_page_url} />
      </div>
    </AdminShell>
  );
}
