import type { ComponentProps } from 'react';
import { Slot } from 'radix-ui';
import { cn } from '@/lib/utils';

/**
 * Gaya Tabs shadcn untuk navigasi antarhalaman (Masuk/Daftar). Bukan Radix Tabs karena tiap tab
 * adalah halaman sendiri: pakai `asChild` dengan `Link`, penanda aktif lewat `data-state="active"`.
 */
function TabsList({ className, ...props }: ComponentProps<'ul'>) {
  return (
    <ul
      data-slot="tabs-list"
      className={cn('inline-flex items-center gap-1 rounded-full border border-border bg-neutral-soft p-1', className)}
      {...props}
    />
  );
}

function TabsTrigger({
  className,
  active = false,
  asChild = false,
  ...props
}: ComponentProps<'a'> & { active?: boolean; asChild?: boolean }) {
  const Comp = asChild ? Slot.Root : 'a';
  return (
    <Comp
      data-slot="tabs-trigger"
      data-state={active ? 'active' : 'inactive'}
      aria-current={active ? 'page' : undefined}
      className={cn(
        "relative inline-flex h-8 items-center justify-center gap-1.5 rounded-full px-3.5 text-[0.8125rem] font-medium whitespace-nowrap text-text-secondary transition-all duration-300 ease-[cubic-bezier(0.2,0.7,0.2,1)] after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-[''] hover:bg-card/60 hover:text-nav-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring data-[state=active]:bg-card data-[state=active]:text-text data-[state=active]:shadow-lift",
        className,
      )}
      {...props}
    />
  );
}

export { TabsList, TabsTrigger };
