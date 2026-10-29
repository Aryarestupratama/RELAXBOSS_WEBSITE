import type { ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';
import TablePager from '@/Components/shared/TablePager';
import { Badge } from '@/Components/ui/badge';
import { Card } from '@/Components/ui/card';
import { MOBILE_CARD } from '@/lib/adminTable';
import { cn } from '@/lib/utils';

type DataTableCardProps = {
  /** Dipakai untuk `aria-labelledby`; harus unik di halaman. */
  id: string;
  title: string;
  description?: ReactNode;
  /** Mis. "5 di halaman ini". Kosongkan bila tidak ada baris. */
  countLabel?: string;
  /** Biasanya `TableToolbar`. Tetap tampil saat daftar kosong agar saringan bisa diubah. */
  toolbar?: ReactNode;
  /** Status kosong; ditampilkan menggantikan `children` bila `isEmpty`. */
  isEmpty: boolean;
  empty: { icon: LucideIcon; message: string; action?: ReactNode };
  pager?: { label: string; prevUrl: string | null; nextUrl: string | null };
  /** Biasanya `DataTable`. */
  children: ReactNode;
};

/**
 * Pola daftar admin: judul dan hitungan, lalu satu kartu berisi bilah alat, tabel, dan paginasi.
 * Di HP kartu induk "melebur" (`max-md:contents`) sehingga bilah alat dan tiap baris tampil sebagai kartu sendiri-sendiri.
 */
export default function DataTableCard({
  id,
  title,
  description,
  countLabel,
  toolbar,
  isEmpty,
  empty,
  pager,
  children,
}: DataTableCardProps) {
  const EmptyIcon = empty.icon;
  const titleId = `${id}-judul`;

  return (
    <section aria-labelledby={titleId} data-load className="flex flex-col gap-3">
      <div className="flex flex-wrap items-end justify-between gap-x-3 gap-y-1">
        <div>
          <h2 id={titleId} className="text-lg leading-snug font-bold tracking-tight">
            {title}
          </h2>
          {description ? <p className="mt-0.5 text-sm text-text-secondary">{description}</p> : null}
        </div>
        {countLabel ? <Badge variant="outline">{countLabel}</Badge> : null}
      </div>

      <Card className="gap-0 p-0 max-md:contents">
        {toolbar ? (
          <div className={cn(MOBILE_CARD, 'p-3 md:border-b md:border-border/70 md:px-4 md:py-3')}>{toolbar}</div>
        ) : null}

        {isEmpty ? (
          <div className={cn(MOBILE_CARD, 'flex flex-col items-center gap-3 px-6 py-10 text-center md:py-12')}>
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <EmptyIcon className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">{empty.message}</p>
            {empty.action}
          </div>
        ) : (
          children
        )}

        {pager ? (
          <TablePager
            label={pager.label}
            prevUrl={pager.prevUrl}
            nextUrl={pager.nextUrl}
            className="md:border-t md:border-border/70 md:px-4 md:py-3"
          />
        ) : null}
      </Card>
    </section>
  );
}
