import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Shuffle, MessagesSquare, TriangleAlert } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import MonitoringFilter from '@/Components/shared/MonitoringFilter';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringPager from '@/Components/shared/MonitoringPager';
import MonitoringPseudoId from '@/Components/shared/MonitoringPseudoId';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { formatDate, type ChatFilter, type ConversationRow } from '@/lib/adminMonitoring';

type ChatIndexProps = {
  filter: ChatFilter;
  rows: ConversationRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const FILTERS: { key: ChatFilter; label: string }[] = [
  { key: 'krisis', label: 'Krisis' },
  { key: 'rendah', label: 'Keyakinan rendah' },
  { key: 'acak', label: 'Sampel acak' },
];

const EMPTY: Record<ChatFilter, string> = {
  krisis: 'Belum ada Percakapan bertanda krisis.',
  rendah: 'Belum ada balasan dengan keyakinan rendah.',
  acak: 'Belum ada Percakapan untuk disampel.',
};

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
const PILL_BUTTON = 'rounded-full! px-5 text-sm';

function Flags({ row }: { row: ConversationRow }) {
  if (!row.has_crisis && !row.low_confidence) {
    return <span className="text-text-secondary">-</span>;
  }

  return (
    <span className="flex flex-wrap gap-1.5">
      {row.has_crisis ? (
        <Badge variant="crisis">
          <TriangleAlert aria-hidden="true" />
          Krisis
        </Badge>
      ) : null}
      {row.low_confidence && row.lowest_confidence !== null ? (
        <Badge variant="peach">Keyakinan terendah {row.lowest_confidence}%</Badge>
      ) : null}
    </span>
  );
}

function OpenButton({ row }: { row: ConversationRow }) {
  return (
    <Button asChild variant="outline" className={PILL_BUTTON}>
      <Link href={`/admin/monitoring/chat/${row.id}`} aria-label={`Buka Percakapan ${row.pseudo_id}`}>
        Buka
        <ArrowRight aria-hidden="true" />
      </Link>
    </Button>
  );
}

/** SCR-022, tab Chat: daftar Percakapan tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function ChatIndex({ filter, rows, prev_page_url, next_page_url }: ChatIndexProps) {
  return (
    <AdminShell title="Monitoring Chat" staggered>
      <MonitoringHeader current="chat" />

      <div className="flex flex-col gap-6">
        <MonitoringFilter label="Saringan Percakapan" basePath="/admin/monitoring/chat" options={FILTERS} current={filter} />

        {rows.length === 0 ? (
          <Card tone="lavender" data-load className="items-center gap-3 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <MessagesSquare className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">{EMPTY[filter]}</p>
          </Card>
        ) : (
          <section aria-labelledby="daftar-percakapan" data-load className="flex flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
              <div>
                <h2 id="daftar-percakapan" className="text-lg leading-snug font-bold tracking-tight">
                  Percakapan untuk ditinjau
                </h2>
                {filter === 'acak' ? (
                  <p className="mt-0.5 inline-flex items-center gap-1.5 text-sm text-text-secondary">
                    <Shuffle className="size-3.5" aria-hidden="true" />
                    Sampel berganti setiap halaman dimuat ulang.
                  </p>
                ) : null}
              </div>
              <Badge variant="outline">{rows.length} di halaman ini</Badge>
            </div>

            {/* >= 768px: tabel */}
            <Card className="hidden gap-0 p-3 md:flex">
              <Table className="min-w-[40rem]">
                <TableCaption className="sr-only">Daftar Percakapan untuk ditinjau</TableCaption>
                <TableHeader>
                  <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                    <TableHead>ID semu</TableHead>
                    <TableHead>Tanggal</TableHead>
                    <TableHead>Giliran</TableHead>
                    <TableHead>Intent awal</TableHead>
                    <TableHead>Penanda</TableHead>
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
                      <TableCell>{row.total_turns}</TableCell>
                      <TableCell>
                        {row.initial_intent ? <Badge variant="outline">{row.initial_intent}</Badge> : <span className="text-text-secondary">-</span>}
                      </TableCell>
                      <TableCell>
                        <Flags row={row} />
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
                    <p className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-text-secondary">
                      <span className="inline-flex items-center gap-1.5">
                        <CalendarDays className="size-4" aria-hidden="true" />
                        {formatDate(row.date)}
                      </span>
                      <span>{row.total_turns} giliran</span>
                      {row.initial_intent ? <span>Intent: {row.initial_intent}</span> : null}
                    </p>
                    {row.has_crisis || row.low_confidence ? <Flags row={row} /> : null}
                    <div>
                      <OpenButton row={row} />
                    </div>
                  </Card>
                </li>
              ))}
            </ul>
          </section>
        )}

        <MonitoringPager label="Halaman Percakapan" prevUrl={prev_page_url} nextUrl={next_page_url} />
      </div>
    </AdminShell>
  );
}
