import * as React from 'react';
import { Toast as ToastPrimitive } from 'radix-ui';
import { cva, type VariantProps } from 'class-variance-authority';
import { X } from 'lucide-react';
import { cn } from '@/lib/utils';

/**
 * Toast gaya shadcn di atas Radix Toast (Design CMP-013). Radix Toast tidak menyuntik <style>, jadi lolos CSP
 * (`style-src` bernonce); atribut `style` yang dipakainya diizinkan `style-src-attr`.
 * Warna hanya dari token (RULE-050): kartu `--card`, garis kiri sebagai penanda nada (selalu disertai ikon dan teks).
 */
const ToastProvider = ToastPrimitive.Provider;

function ToastViewport({ className, ...props }: React.ComponentProps<typeof ToastPrimitive.Viewport>) {
  return (
    <ToastPrimitive.Viewport
      label="Notifikasi ({hotkey})"
      className={cn(
        'fixed top-0 right-0 left-0 z-100 flex max-h-dvh w-full flex-col gap-2 p-4 pt-[max(1rem,env(safe-area-inset-top))] outline-none sm:left-auto sm:max-w-sm',
        className,
      )}
      {...props}
    />
  );
}

const toastVariants = cva(
  'group pointer-events-auto relative flex w-full items-start gap-3 overflow-hidden rounded-2xl border border-l-4 border-border/60 bg-card p-4 pr-10 text-sm text-text shadow-popover motion-safe:data-[state=open]:animate-in motion-safe:data-[state=open]:fade-in-0 motion-safe:data-[state=open]:slide-in-from-top-4 motion-safe:data-[state=closed]:animate-out motion-safe:data-[state=closed]:fade-out-0 motion-safe:data-[state=closed]:slide-out-to-right-full motion-safe:data-[swipe=end]:animate-out motion-safe:data-[swipe=end]:slide-out-to-right-full data-[swipe=cancel]:translate-x-0 data-[swipe=move]:translate-x-(--radix-toast-swipe-move-x) data-[swipe=move]:transition-none',
  {
    variants: {
      tone: {
        success: 'border-l-success',
        error: 'border-l-destructive',
        info: 'border-l-primary',
      },
    },
    defaultVariants: { tone: 'info' },
  },
);

function Toast({
  className,
  tone,
  ...props
}: React.ComponentProps<typeof ToastPrimitive.Root> & VariantProps<typeof toastVariants>) {
  return <ToastPrimitive.Root className={cn(toastVariants({ tone }), className)} {...props} />;
}

function ToastDescription({ className, ...props }: React.ComponentProps<typeof ToastPrimitive.Description>) {
  return <ToastPrimitive.Description className={cn('min-w-0 flex-1 leading-relaxed', className)} {...props} />;
}

function ToastClose({ className, ...props }: React.ComponentProps<typeof ToastPrimitive.Close>) {
  return (
    <ToastPrimitive.Close
      aria-label="Tutup notifikasi"
      className={cn(
        'absolute top-1.5 right-1.5 grid size-9 place-items-center rounded-full text-text-secondary transition-colors hover:bg-surface-alt hover:text-nav-hover',
        className,
      )}
      {...props}
    >
      <X className="size-4" aria-hidden="true" />
    </ToastPrimitive.Close>
  );
}

export { Toast, ToastClose, ToastDescription, ToastProvider, ToastViewport };
