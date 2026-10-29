import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, MessagesSquare, Shuffle, TriangleAlert } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import DataTable, { type DataTableColumn } from '@/Components/shared/DataTable';
import DataTableCard from '@/Components/shared/DataTableCard';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringPseudoId from '@/Components/shared/MonitoringPseudoId';
import TableFilterTabs from '@/Components/shared/TableFilterTabs';
import TableToolbar from '@/Components/shared/TableToolbar';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { formatDate, type ChatFilter, type ConversationRow } from '@/lib/adminMonitoring';
import { ROW_BUTTON } from '@/lib/adminTable';

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
    <Button asChild variant="outline" className={ROW_BUTTON}>
      <Link href={`/admin/monitoring/chat/${row.id}`} aria-label={`Buka Percakapan ${row.pseudo_id}`}>
        Buka
        <ArrowRight aria-hidden="true" />
      </Link>
    </Button>
  );
}

const COLUMNS: DataTableColumn<ConversationRow>[] = [
  { id: 'pseudo', header: 'ID semu', rowHeader: true, cell: (row) => <MonitoringPseudoId value={row.pseudo_id} /> },
  { id: 'date', header: 'Tanggal', className: 'whitespace-nowrap text-text-secondary', cell: (row) => formatDate(row.date) },
  { id: 'turns', header: 'Giliran', cell: (row) => row.total_turns },
  {
    id: 'intent',
    header: 'Intent awal',
    cell: (row) =>
      row.initial_intent ? <Badge variant="outline">{row.initial_intent}</Badge> : <span className="text-text-secondary">-</span>,
  },
  { id: 'flags', header: 'Penanda', cell: (row) => <Flags row={row} /> },
  { id: 'open', header: 'Aksi', hideHeader: true, align: 'right', cell: (row) => <OpenButton row={row} /> },
];

/** SCR-022, tab Chat: daftar Percakapan tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function ChatIndex({ filter, rows, prev_page_url, next_page_url }: ChatIndexProps) {
  return (
    <AdminShell title="Monitoring Chat" staggered>
      <MonitoringHeader current="chat" />

      <DataTableCard
        id="daftar-percakapan"
        title="Percakapan untuk ditinjau"
        description={
          filter === 'acak' ? (
            <span className="inline-flex items-center gap-1.5">
              <Shuffle className="size-3.5" aria-hidden="true" />
              Sampel berganti setiap halaman dimuat ulang.
            </span>
          ) : undefined
        }
        countLabel={rows.length > 0 ? `${rows.length} di halaman ini` : undefined}
        toolbar={
          <TableToolbar>
            <TableFilterTabs label="Saringan Percakapan" basePath="/admin/monitoring/chat" options={FILTERS} current={filter} />
          </TableToolbar>
        }
        isEmpty={rows.length === 0}
        empty={{ icon: MessagesSquare, message: EMPTY[filter] }}
        pager={{ label: 'Halaman Percakapan', prevUrl: prev_page_url, nextUrl: next_page_url }}
      >
        <DataTable
          caption="Daftar Percakapan untuk ditinjau"
          columns={COLUMNS}
          rows={rows}
          getRowKey={(row) => row.id}
          mobileCard={(row) => (
            <>
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
            </>
          )}
        />
      </DataTableCard>
    </AdminShell>
  );
}
