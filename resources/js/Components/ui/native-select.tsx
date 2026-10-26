import type { ComponentProps } from 'react';
import { ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

type NativeSelectProps = ComponentProps<'select'> & {
  /** `default`: tampilan sama dengan Input (dipakai formulir Admin). `pill`: bentuk pil dengan panah, untuk area pengguna. */
  variant?: 'default' | 'pill';
};

/**
 * Select bawaan peramban (tetap aman untuk CSP dan keyboard) dengan tampilan gaya shadcn.
 * Tinggi min. 44px, cincin fokus dari app.css. Galat ditandai lewat aria-invalid (diatur FormField).
 */
function NativeSelect({ className = '', children, variant = 'default', ...props }: NativeSelectProps) {
  if (variant === 'pill') {
    return (
      <div data-slot="native-select-wrapper" className="relative inline-flex w-full sm:w-auto">
        <select
          data-slot="native-select"
          className={cn(
            'min-h-11 w-full cursor-pointer appearance-none rounded-full border-[1.5px] border-input bg-card py-2 pr-10 pl-4 text-sm text-foreground transition-colors hover:bg-neutral-soft disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive',
            className,
          )}
          {...props}
        >
          {children}
        </select>
        <ChevronDown
          className="pointer-events-none absolute top-1/2 right-3.5 size-4 -translate-y-1/2 text-text-secondary"
          aria-hidden="true"
        />
      </div>
    );
  }

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
