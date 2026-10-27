import type { ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';

type EmptyStateProps = {
  icon: LucideIcon;
  title?: string;
  message: string;
  action?: ReactNode;
};

/**
 * CMP-010. Empty state sederhana: ikon Lucide di tengah lingkaran konsentris, judul opsional, pesan hangat, satu aksi.
 * Tanpa ilustrasi gambar. Tanpa kartu sendiri: pemanggil membungkusnya dengan `Card` bila perlu.
 * Warna hanya dari token; lingkaran diturunkan dari `--primary` lewat opasitas (RULE-050).
 */
export default function EmptyState({ icon: Icon, title, message, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center text-center">
      <div className="relative grid size-40 place-items-center" aria-hidden="true">
        <span className="absolute size-40 rounded-full border border-primary/5" />
        <span className="absolute size-30 rounded-full border border-primary/10" />
        <span className="grid size-20 place-items-center rounded-full border border-primary/15 bg-soft-lavender text-primary">
          <Icon className="size-8" strokeWidth={1.75} />
        </span>
      </div>
      {title ? <p className="mt-2 text-base font-semibold text-text">{title}</p> : null}
      <p className="mt-1 max-w-sm text-sm text-text-secondary">{message}</p>
      {action ? <div className="mt-5">{action}</div> : null}
    </div>
  );
}
