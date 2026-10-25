import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * Input RelaxBoss: tinggi min. 44px, cincin fokus dari aturan global app.css.
 * Galat ditandai lewat aria-invalid (diatur FormField). `className` boleh menimpa kelas dasar
 * (halaman Masuk dan Daftar memakai ukuran lebih ringkas lewat `AUTH_INPUT`).
 */
function Input({ className, type = 'text', ...props }: ComponentProps<'input'>) {
  return (
    <input
      type={type}
      data-slot="input"
      className={cn(
        'min-h-11 w-full rounded-lg border-[1.5px] border-input bg-card px-3.5 py-2 text-base text-foreground placeholder:text-text-muted disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive',
        className,
      )}
      {...props}
    />
  );
}

export { Input };
