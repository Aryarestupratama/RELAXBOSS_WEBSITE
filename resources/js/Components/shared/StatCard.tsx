import type { LucideIcon } from 'lucide-react';
import { Card } from '@/Components/ui/card';
import { cn } from '@/lib/utils';

type StatTone = 'lavender' | 'sky' | 'mint' | 'peach' | 'crisis';

type StatCardProps = {
  label: string;
  /** Sudah diformat server (angka di bawah batas kelompok tampil sebagai "<5"). */
  value: string;
  icon: LucideIcon;
  tone?: StatTone;
  className?: string;
};

/**
 * Kartu angka agregat (Admin). Harus berada di dalam `<dl>`: label = `dt`, angka = `dd`.
 * Pastel `--soft-*` dengan teks `--text`; `crisis` memakai `--crisis-bg` dan `--crisis-text` (RULE-050/052).
 */
export default function StatCard({ label, value, icon: Icon, tone = 'lavender', className }: StatCardProps) {
  const crisis = tone === 'crisis';
  return (
    <Card
      tone={crisis ? 'default' : tone}
      className={cn(
        'gap-4 p-5',
        crisis && 'border-crisis-text/15 bg-crisis-bg text-crisis-text',
        className,
      )}
    >
      <div className="flex items-start justify-between gap-3">
        <dt className={cn('text-sm font-medium', crisis ? 'text-crisis-text' : 'text-text-secondary')}>{label}</dt>
        <span
          className={cn('grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender', crisis ? 'text-crisis-text' : 'text-primary')}
          aria-hidden="true"
        >
          <Icon className="size-5" />
        </span>
      </div>
      <dd className="text-3xl leading-none font-bold tracking-tight tabular-nums">{value}</dd>
    </Card>
  );
}
