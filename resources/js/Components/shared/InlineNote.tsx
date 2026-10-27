import type { ComponentProps, ReactNode } from 'react';
import { Info, type LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

type InlineNoteProps = Omit<ComponentProps<'div'>, 'children'> & {
  children: ReactNode;
  /** `warning` untuk peringatan (mis. artikel contoh); selain itu `info`. */
  tone?: 'info' | 'warning';
  icon?: LucideIcon;
};

/**
 * Catatan satu kalimat selebar kontennya (padanan Blade: `x-inline-note`).
 * Isi MUST satu kalimat pendek. Tautan di dalam `children` otomatis bergaya.
 */
export default function InlineNote({ children, tone = 'info', icon: Icon = Info, className, ...props }: InlineNoteProps) {
  return (
    <div
      role="note"
      className={cn(
        'flex w-full items-start gap-3 rounded-2xl border bg-surface-alt px-4 py-3 text-sm leading-relaxed text-text md:px-5 md:py-3.5',
        '[&_a]:font-semibold [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4 [&_a]:hover:text-nav-hover',
        tone === 'warning' ? 'border-warning/50' : 'border-border/60',
        className,
      )}
      {...props}
    >
      <Icon className={cn('mt-0.5 size-5 shrink-0', tone === 'warning' ? 'text-warning' : 'text-primary')} aria-hidden="true" />
      <p className="min-w-0 flex-1">{children}</p>
    </div>
  );
}
