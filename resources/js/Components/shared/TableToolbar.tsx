import type { ReactNode } from 'react';

type TableToolbarProps = {
  /** Sisi kiri: pencarian dan/atau saringan. */
  children: ReactNode;
  /** Sisi kanan: aksi tambahan bila ada. */
  actions?: ReactNode;
};

/** Bilah alat di atas tabel: pencarian dan saringan di kiri, aksi di kanan, turun baris di layar sempit. */
export default function TableToolbar({ children, actions }: TableToolbarProps) {
  return (
    <div className="flex flex-wrap items-center justify-between gap-x-4 gap-y-3">
      <div className="flex min-w-0 flex-1 flex-wrap items-center gap-x-4 gap-y-3">{children}</div>
      {actions ? <div className="flex flex-wrap items-center gap-2">{actions}</div> : null}
    </div>
  );
}
