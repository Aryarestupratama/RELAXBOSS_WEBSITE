import type { ComponentProps } from 'react';

/**
 * Select bawaan peramban dengan tampilan yang sama dengan Input: tinggi min. 44px, cincin fokus dari app.css.
 * Galat ditandai lewat aria-invalid (diatur FormField).
 */
function NativeSelect({ className = '', children, ...props }: ComponentProps<'select'>) {
  return (
    <select
      data-slot="native-select"
      className={`min-h-11 w-full rounded-lg border-[1.5px] border-input bg-card px-3.5 py-2 text-base text-foreground disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive ${className}`}
      {...props}
    >
      {children}
    </select>
  );
}

export { NativeSelect };
