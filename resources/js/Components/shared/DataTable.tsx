import type { ReactNode } from 'react';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { cn } from '@/lib/utils';

export type DataTableColumn<T> = {
  id: string;
  header: string;
  /** Sembunyikan judul kolom secara visual (tetap terbaca pembaca layar), mis. kolom Aksi. */
  hideHeader?: boolean;
  align?: 'left' | 'right';
  /** Sel pertama yang mengenali baris dirender sebagai `th scope="row"`. */
  rowHeader?: boolean;
  /** Kelas tambahan untuk sel, mis. `whitespace-nowrap text-text-secondary` atau `tabular-nums`. */
  className?: string;
  cell: (row: T) => ReactNode;
};

type DataTableProps<T> = {
  /** Keterangan tabel untuk pembaca layar. */
  caption: string;
  columns: DataTableColumn<T>[];
  rows: T[];
  getRowKey: (row: T) => string | number;
  /** Isi satu kartu di layar < 768px (tanpa pembungkus kartu; `DataTable` yang membungkusnya). */
  mobileCard: (row: T) => ReactNode;
  /** Lebar minimum tabel; di bawahnya tabel menggulir di dalam wadahnya sendiri. */
  minWidthClass?: string;
};

const HEADER_ROW =
  'border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl';
const ROW_HEAD = 'h-auto px-3 py-2 text-sm font-normal tracking-normal whitespace-normal text-text';

/**
 * Tabel admin yang dirender dari satu sumber: tabel padat di layar >= 768px dan daftar kartu di bawahnya.
 * Dipakai di dalam `DataTableCard`. Kartu pembungkusnya mengurus bayangan di desktop, jadi tabel tidak punya kartu sendiri.
 */
export default function DataTable<T>({
  caption,
  columns,
  rows,
  getRowKey,
  mobileCard,
  minWidthClass = 'min-w-[40rem]',
}: DataTableProps<T>) {
  return (
    <>
      {/* >= 768px: tabel */}
      <div className="hidden p-2 md:block">
        <Table className={minWidthClass}>
          <TableCaption className="sr-only">{caption}</TableCaption>
          <TableHeader>
            <TableRow className={HEADER_ROW}>
              {columns.map((column) => (
                <TableHead key={column.id} className={cn(column.align === 'right' && 'text-right')}>
                  {column.hideHeader ? <span className="sr-only">{column.header}</span> : column.header}
                </TableHead>
              ))}
            </TableRow>
          </TableHeader>
          <TableBody>
            {rows.map((row) => (
              <TableRow key={getRowKey(row)}>
                {columns.map((column) =>
                  column.rowHeader ? (
                    <TableHead key={column.id} scope="row" className={ROW_HEAD}>
                      {column.cell(row)}
                    </TableHead>
                  ) : (
                    <TableCell
                      key={column.id}
                      className={cn('py-2', column.align === 'right' && 'text-right', column.className)}
                    >
                      {column.cell(row)}
                    </TableCell>
                  ),
                )}
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      {/* < 768px: kartu */}
      <ul className="grid grid-cols-1 gap-3 md:hidden">
        {rows.map((row) => (
          <li key={getRowKey(row)}>
            <Card className="gap-3 p-4">{mobileCard(row)}</Card>
          </li>
        ))}
      </ul>
    </>
  );
}
