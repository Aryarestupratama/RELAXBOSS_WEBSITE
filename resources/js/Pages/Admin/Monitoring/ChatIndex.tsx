import { Link } from '@inertiajs/react';
import { MessagesSquare } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import EmptyState from '@/Components/shared/EmptyState';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import { Button } from '@/Components/ui/button';
import { formatDate, type ChatFilter, type ConversationRow } from '@/lib/adminMonitoring';

type ChatIndexProps = {
  filter: ChatFilter;
  rows: ConversationRow[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

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

/** SCR-022, tab Chat: daftar Percakapan tanpa isi dan tanpa pemilik. Isi dibuka lewat halaman detail (tercatat). */
export default function ChatIndex({ filter, rows, prev_page_url, next_page_url }: ChatIndexProps) {
  return (
    <AdminShell title="Monitoring Chat">
      <MonitoringHeader current="chat" />

      <div className="mt-6 space-y-4">
        <nav aria-label="Saringan Percakapan" className="flex flex-wrap gap-2">
          {FILTERS.map(({ key, label }) => (
            <Button key={key} asChild variant={key === filter ? 'default' : 'outline'}>
              <Link href={`/admin/monitoring/chat?saring=${key}`} aria-current={key === filter ? 'true' : undefined}>
                {label}
              </Link>
            </Button>
          ))}
        </nav>

        {filter === 'acak' && rows.length > 0 ? (
          <p className="text-sm text-text-secondary">Sampel berganti setiap halaman dimuat ulang.</p>
        ) : null}

        {rows.length === 0 ? (
          <EmptyState icon={MessagesSquare} message={EMPTY[filter]} />
        ) : (
          <div className="overflow-x-auto rounded-xl border border-border bg-card">
            <table className="w-full min-w-[40rem] text-left">
              <caption className="sr-only">Daftar Percakapan untuk ditinjau</caption>
              <thead className="border-b border-border text-sm text-text-secondary">
                <tr>
                  <th scope="col" className="px-4 py-3 font-medium">
                    ID semu
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Tanggal
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Giliran
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Intent awal
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Penanda
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
                    <td className="px-4 py-3">{row.total_turns}</td>
                    <td className="px-4 py-3">{row.initial_intent ?? '-'}</td>
                    <td className="px-4 py-3">
                      <span className="flex flex-wrap gap-1">
                        {row.has_crisis ? (
                          <span className={`${badgeBase} bg-neutral-soft text-brand-strong`}>Krisis</span>
                        ) : null}
                        {row.low_confidence && row.lowest_confidence !== null ? (
                          <span className={`${badgeBase} bg-muted text-text-secondary`}>
                            Keyakinan terendah {row.lowest_confidence}%
                          </span>
                        ) : null}
                        {!row.has_crisis && !row.low_confidence ? <span className="text-text-secondary">-</span> : null}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">
                      <Button asChild variant="outline">
                        <Link href={`/admin/monitoring/chat/${row.id}`} aria-label={`Buka Percakapan ${row.pseudo_id}`}>
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
          <nav aria-label="Halaman Percakapan" className="flex justify-between gap-3">
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
