import type { ReactNode } from 'react';

type MonitoringItemProps = {
  /** Judul kecil di atas isi, mis. "Pengguna" atau "RelaxMate". */
  title: string;
  /** Penanda (intent, keyakinan, krisis) sebagai teks, bukan hanya warna. */
  badges?: ReactNode;
  children: ReactNode;
};

/** CMP-020. Satu butir tinjauan admin: judul, isi sebagai teks biasa, dan penanda. Hanya baca. */
export default function MonitoringItem({ title, badges, children }: MonitoringItemProps) {
  return (
    <li className="rounded-xl border border-border bg-card p-4">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <h3 className="text-base font-medium">{title}</h3>
        {badges ? <div className="flex flex-wrap gap-1">{badges}</div> : null}
      </div>
      <div className="mt-2 whitespace-pre-wrap break-words">{children}</div>
    </li>
  );
}
