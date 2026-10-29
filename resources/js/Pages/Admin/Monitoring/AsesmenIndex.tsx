import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CircleCheck, CircleDashed, ClipboardList } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import DataTable, { type DataTableColumn } from '@/Components/shared/DataTable';
import DataTableCard from '@/Components/shared/DataTableCard';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringPseudoId from '@/Components/shared/MonitoringPseudoId';
import TableFilterTabs from '@/Components/shared/TableFilterTabs';
import TableToolbar from '@/Components/shared/TableToolbar';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { formatDate, type AttemptFilter, type AttemptRow } from '@/lib/adminMonitoring';
import { ROW_BUTTON } from '@/lib/adminTable';

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
    <Button asChild variant="outline" className={ROW_BUTTON}>
      <Link href={`/admin/monitoring/asesmen/${row.id}`} aria-label={`Buka hasil ${row.pseudo_id}`}>
        Buka
        <ArrowRight aria-hidden="true" />
      </Link>
    </Button>
  );
}

const COLUMNS: DataTableColumn<AttemptRow>[] = [
  { id: 'pseudo', header: 'ID semu', rowHeader: true, cell: (row) => <MonitoringPseudoId value={row.pseudo_id} /> },
  { id: 'date', header: 'Tanggal', className: 'whitespace-nowrap text-text-secondary', cell: (row) => formatDate(row.date) },
  { id: 'assessment', header: 'Asesmen', className: 'font-medium', cell: (row) => row.assessment },
  { id: 'ai', header: 'Rekomendasi AI', cell: (row) => <AiBadge row={row} /> },
  { id: 'open', header: 'Aksi', hideHeader: true, align: 'right', cell: (row) => <OpenButton row={row} /> },
];

/** SCR-022, tab Asesmen: daftar hasil tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function AsesmenIndex({ filter, rows, prev_page_url, next_page_url }: AsesmenIndexProps) {
  return (
    <AdminShell title="Monitoring Asesmen" staggered>
      <MonitoringHeader current="asesmen" />

      <DataTableCard
        id="daftar-hasil"
        title="Hasil untuk ditinjau"
        countLabel={rows.length > 0 ? `${rows.length} di halaman ini` : undefined}
        toolbar={
          <TableToolbar>
            <TableFilterTabs label="Saringan hasil Asesmen" basePath="/admin/monitoring/asesmen" options={FILTERS} current={filter} />
          </TableToolbar>
        }
        isEmpty={rows.length === 0}
        empty={{ icon: ClipboardList, message: EMPTY[filter] }}
        pager={{ label: 'Halaman hasil Asesmen', prevUrl: prev_page_url, nextUrl: next_page_url }}
      >
        <DataTable
          caption="Daftar hasil Asesmen untuk ditinjau"
          columns={COLUMNS}
          rows={rows}
          getRowKey={(row) => row.id}
          minWidthClass="min-w-[36rem]"
          mobileCard={(row) => (
            <>
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
            </>
          )}
        />
      </DataTableCard>
    </AdminShell>
  );
}
