import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * Card bergaya shadcn. Warna hanya dari token (RULE-050/052); bayangan memakai --shadow-lift.
 * Keputusan founder (TASK-028AB): SEMUA kartu putih (--card) dengan bayangan. Warna di dalam kartu hanya ungu muda
 * (--soft-lavender) pada kotak ikon, lencana, dan hiasan. Prop `tone` dipertahankan agar pemanggil lama tidak berubah,
 * tetapi tidak lagi mengubah latar.
 */
type CardTone = 'default' | 'lavender' | 'sky' | 'mint' | 'peach' | 'muted';

const TONES: Record<CardTone, string> = {
  default: 'bg-card',
  lavender: 'bg-card',
  sky: 'bg-card',
  mint: 'bg-card',
  peach: 'bg-card',
  muted: 'bg-card',
};

function Card({ className, tone = 'default', ...props }: ComponentProps<'div'> & { tone?: CardTone }) {
  return (
    <div
      data-slot="card"
      className={cn('flex flex-col rounded-3xl border border-border/60 text-text shadow-lift', TONES[tone], className)}
      {...props}
    />
  );
}

function CardHeader({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="card-header" className={cn('flex flex-col gap-1.5', className)} {...props} />;
}

function CardTitle({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="card-title" className={cn('leading-snug font-semibold', className)} {...props} />;
}

function CardDescription({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="card-description" className={cn('text-sm text-text-secondary', className)} {...props} />;
}

function CardContent({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="card-content" className={cn(className)} {...props} />;
}

function CardFooter({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="card-footer" className={cn('mt-auto flex items-center', className)} {...props} />;
}

export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter };
