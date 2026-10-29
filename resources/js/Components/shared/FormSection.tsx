import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { LoaderCircle, Save, type LucideIcon } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { cn } from '@/lib/utils';

export type FormSectionTone = 'lavender' | 'sky' | 'mint' | 'peach';

const ICON_TONE: Record<FormSectionTone, string> = {
  lavender: 'bg-soft-lavender',
  sky: 'bg-soft-sky',
  mint: 'bg-soft-mint',
  peach: 'bg-soft-peach',
};

type FormSectionProps = {
  /** Dipakai sebagai jangkar (`#id`) dan dasar `id` judul. */
  id: string;
  title: string;
  intro?: string;
  icon: LucideIcon;
  tone: FormSectionTone;
  /** Penanda kecil di kanan judul (mis. jumlah butir). */
  aside?: ReactNode;
  children: ReactNode;
};

/** Satu bagian formulir Admin = satu `Card`: kepala berikon, `Separator`, lalu isi. */
export default function FormSection({ id, title, intro, icon: Icon, tone, aside, children }: FormSectionProps) {
  return (
    <section id={id} aria-labelledby={`${id}-title`} className="scroll-mt-6">
      <Card className="gap-0">
        <CardHeader className="flex-row items-start gap-3 p-5 md:p-6">
          <span
            className={cn('grid size-11 shrink-0 place-items-center rounded-full text-text', ICON_TONE[tone])}
            aria-hidden="true"
          >
            <Icon className="size-5" />
          </span>
          <div className="min-w-0 flex-1">
            <h2 id={`${id}-title`} className="text-lg leading-snug font-bold tracking-tight">
              {title}
            </h2>
            {intro ? <CardDescription className="mt-0.5">{intro}</CardDescription> : null}
          </div>
          {aside}
        </CardHeader>
        <Separator />
        <CardContent className="space-y-5 p-5 md:p-6">{children}</CardContent>
      </Card>
    </section>
  );
}

type SectionLink = { id: string; label: string; icon: LucideIcon };

/** Daftar loncat ke bagian (>= 1024px, menempel di kiri). Tautan `#id` biasa, tanpa JS. */
export function FormSectionNav({ links }: { links: SectionLink[] }) {
  return (
    <nav aria-label="Bagian formulir" className="hidden lg:sticky lg:top-6 lg:block">
      <Card className="gap-1 p-2">
        <p className="px-3 pt-2 pb-1 text-xs font-semibold tracking-wide text-text-secondary">Di halaman ini</p>
        <ul>
          {links.map(({ id, label, icon: Icon }) => (
            <li key={id}>
              <a
                href={`#${id}`}
                className="flex min-h-11 items-center gap-2.5 rounded-xl px-3 text-sm font-medium transition-colors hover:bg-soft-lavender/50"
              >
                <Icon className="size-4 text-text-secondary" aria-hidden="true" />
                {label}
              </a>
            </li>
          ))}
        </ul>
      </Card>
    </nav>
  );
}

type FormSaveBarProps = {
  statusText: string;
  hasErrors: boolean;
  processing: boolean;
  cancelHref: string;
  submitLabel: string;
};

/** Bar simpan yang menempel di bawah; di HP naik di atas bar tab AdminShell. Harus di dalam `<form>`. */
export function FormSaveBar({ statusText, hasErrors, processing, cancelHref, submitLabel }: FormSaveBarProps) {
  return (
    <div className="sticky bottom-20 z-30 md:bottom-4">
      <Card className="flex-row flex-wrap items-center justify-between gap-3 rounded-2xl bg-card/90 px-4 py-3 shadow-popover backdrop-blur">
        <p aria-live="polite" className={cn('text-sm font-medium', hasErrors ? 'text-destructive' : 'text-text-secondary')}>
          {statusText}
        </p>
        <div className="flex w-full flex-wrap items-center justify-end gap-2 sm:w-auto">
          <Button type="button" variant="ghost" className="rounded-full! px-3 text-sm sm:px-5" asChild>
            <Link href={cancelHref}>Batal</Link>
          </Button>
          <Button type="submit" disabled={processing} className="rounded-full! px-4 text-sm sm:px-6">
            {processing ? (
              <LoaderCircle className="animate-spin motion-reduce:animate-none" aria-hidden="true" />
            ) : (
              <Save aria-hidden="true" />
            )}
            {processing ? 'Menyimpan...' : submitLabel}
          </Button>
        </div>
      </Card>
    </div>
  );
}
