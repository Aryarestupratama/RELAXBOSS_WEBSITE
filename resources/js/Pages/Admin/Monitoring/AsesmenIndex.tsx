import { Link } from '@inertiajs/react';
import { ClipboardList } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import EmptyState from '@/Components/shared/EmptyState';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import { Button } from '@/Components/ui/button';
import { formatDate, type AttemptFilter, type AttemptRow } from '@/lib/adminMonitoring';

type AsesmenIndexProps = {
  filter: AttemptFilter;
  rows: AttemptRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

const FILTERS: { key: AttemptFilter; label: string }[] = [
  { key: 'ai', label: 'Dengan rekomendasi AI' },
  { key: 'semua', label: 'Semua hasil' },
];

const EMPTY: Record<AttemptFilter, string> = {
  ai: 'Belum ada rekomendasi AI untuk ditinjau.',
  semua: 'Belum ada hasil Asesmen.',
};

/** SCR-022, tab Asesmen: daftar hasil tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function AsesmenIndex({ filter, rows, prev_page_url, next_page_url }: AsesmenIndexProps) {
  return (
    <AdminShell title="Monitoring Asesmen">
      <MonitoringHeader current="asesmen" />

      <div className="mt-6 space-y-4">
        <nav aria-label="Saringan hasil Asesmen" className="flex flex-wrap gap-2">
          {FILTERS.map(({ key, label }) => (
            <Button key={key} asChild variant={key === filter ? 'default' : 'outline'}>
              <Link href={`/admin/monitoring/asesmen?saring=${key}`} aria-current={key === filter ? 'true' : undefined}>
                {label}
              </Link>
            </Button>
          ))}
        </nav>

        {rows.length === 0 ? (
          <EmptyState icon={ClipboardList} message={EMPTY[filter]} />
        ) : (
          <div className="overflow-x-auto rounded-xl border border-border bg-card">
            <table className="w-full min-w-[36rem] text-left">
              <caption className="sr-only">Daftar hasil Asesmen untuk ditinjau</caption>
              <thead className="border-b border-border text-sm text-text-secondary">
                <tr>
                  <th scope="col" className="px-4 py-3 font-medium">
                    ID semu
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Tanggal
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Asesmen
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Rekomendasi AI
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    <span className="sr-only">Aksi</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => (
                  <tr key={row.id} className="border-b border-border last:border-b-0">
                    <th scope="row" className="px-4 py-3 font-mono font-medium">
                      {row.pseudo_id}
                    </th>
                    <td className="px-4 py-3">{formatDate(row.date)}</td>
                    <td className="px-4 py-3">{row.assessment}</td>
                    <td className="px-4 py-3">
                      <span
                        className={`${badgeBase} ${
                          row.has_ai_recommendation ? 'bg-neutral-soft text-brand-strong' : 'bg-muted text-text-secondary'
                        }`}
                      >
                        {row.has_ai_recommendation ? 'Ada' : 'Belum ada'}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Button asChild variant="outline">
                        <Link href={`/admin/monitoring/asesmen/${row.id}`} aria-label={`Buka hasil ${row.pseudo_id}`}>
                          Buka
                        </Link>
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {prev_page_url || next_page_url ? (
          <nav aria-label="Halaman hasil Asesmen" className="flex justify-between gap-3">
            {prev_page_url ? (
              <Button asChild variant="outline">
                <Link href={prev_page_url}>Sebelumnya</Link>
              </Button>
            ) : (
              <span />
            )}
            {next_page_url ? (
              <Button asChild variant="outline">
                <Link href={next_page_url}>Berikutnya</Link>
              </Button>
            ) : null}
          </nav>
        ) : null}
      </div>
    </AdminShell>
  );
}
