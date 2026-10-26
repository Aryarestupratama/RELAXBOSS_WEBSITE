import type { ComponentProps } from 'react';
import { X } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

/**
 * Dialog gaya shadcn di atas elemen <dialog> bawaan peramban (bukan Radix): fokus terkunci dan Esc menutup
 * tanpa menyuntik <style>, jadi aman untuk CSP bernonce (Architecture bagian 11). API mengikuti nama shadcn:
 * `Dialog`, `DialogHeader`, `DialogTitle`, `DialogDescription`, `DialogFooter`, `DialogClose`.
 * Pemakai mengatur buka dan tutup lewat `ref` (`showModal()` dan `close()`), `onCancel` untuk Esc,
 * dan `aria-labelledby` serta `aria-describedby`. Warna hanya dari token (RULE-050).
 */
function Dialog({ className, ...props }: ComponentProps<'dialog'>) {
  return (
    <dialog
      data-slot="dialog"
      className={cn(
        'm-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-md overflow-y-auto rounded-3xl border border-border/60 bg-card p-5 text-card-foreground shadow-popover backdrop:bg-primary/40 md:p-6 motion-safe:open:animate-in motion-safe:open:fade-in-0 motion-safe:open:zoom-in-95 motion-safe:open:duration-200',
        className,
      )}
      {...props}
    />
  );
}

function DialogHeader({ className, ...props }: ComponentProps<'div'>) {
  return <div data-slot="dialog-header" className={cn('flex flex-col gap-1.5', className)} {...props} />;
}

function DialogTitle({ className, ...props }: ComponentProps<'h2'>) {
  return <h2 data-slot="dialog-title" className={cn('text-base leading-snug font-bold outline-none md:text-lg', className)} {...props} />;
}

function DialogDescription({ className, ...props }: ComponentProps<'p'>) {
  return <p data-slot="dialog-description" className={cn('text-sm leading-relaxed text-text-secondary', className)} {...props} />;
}

function DialogFooter({ className, ...props }: ComponentProps<'div'>) {
  return (
    <div
      data-slot="dialog-footer"
      className={cn('mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', className)}
      {...props}
    />
  );
}

/** Tombol tutup (ikon X, nama aksesibel \"Tutup\", target 44px). */
function DialogClose({ className, ...props }: Omit<ComponentProps<typeof Button>, 'children' | 'variant' | 'size'>) {
  return (
    <Button
      type="button"
      variant="ghost"
      size="icon"
      aria-label="Tutup"
      data-slot="dialog-close"
      className={cn('-mt-1 -mr-2 shrink-0 rounded-full! text-text-secondary', className)}
      {...props}
    >
      <X aria-hidden="true" />
    </Button>
  );
}

export { Dialog, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose };
