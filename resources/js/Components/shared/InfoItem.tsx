import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type InfoItemProps = {
  label: string;
  /** Nilai kosong (null atau teks kosong) tampil sebagai "Belum diisi". */
  value: ReactNode;
  className?: string;
};

/** Pasangan label dan nilai untuk tampilan data baca-saja. Dipakai di dalam `<dl>`. */
export default function InfoItem({ label, value, className }: InfoItemProps) {
  const empty = value === null || value === undefined || value === '';

  return (
    <div className={cn('min-w-0', className)}>
      <dt className="text-xs font-medium text-text-secondary">{label}</dt>
      <dd className={cn('mt-1 text-sm break-words', empty ? 'text-text-secondary' : 'font-medium text-text')}>
        {empty ? 'Belum diisi' : value}
      </dd>
    </div>
  );
}
