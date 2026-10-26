import type { ComponentProps } from 'react';
import { Slot, Tabs as TabsPrimitive } from 'radix-ui';
import { cn } from '@/lib/utils';

/**
 * Tabs gaya shadcn. Dua keluarga dengan tampilan yang sama:
 * - `Tabs`, `TabsList`, `TabsTrigger`, `TabsContent`: tab ARIA sungguhan (Radix) untuk berganti isi di satu halaman.
 * - `NavTabsList`, `NavTabsTrigger`: tautan antarhalaman (Masuk, Daftar), bukan tab ARIA;
 *   `NavTabsTrigger asChild` dengan `Link`, aktif lewat `active` (`data-state="active"`, `aria-current="page"`).
 * Warna hanya dari token (RULE-050). Area sentuh 44px lewat `after:`.
 */
const LIST_CLASS = 'inline-flex items-center gap-1 rounded-full border border-border bg-card p-1';

const TRIGGER_CLASS =
  "relative inline-flex h-8 items-center justify-center gap-1.5 rounded-full px-3.5 text-[0.8125rem] font-medium whitespace-nowrap text-text-secondary transition-all duration-300 ease-[cubic-bezier(0.2,0.7,0.2,1)] after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-[''] hover:bg-card/60 hover:text-nav-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-card data-[state=active]:text-text data-[state=active]:shadow-lift";

function Tabs({ className, ...props }: ComponentProps<typeof TabsPrimitive.Root>) {
  return <TabsPrimitive.Root data-slot="tabs" className={cn('flex flex-col gap-3', className)} {...props} />;
}

function TabsList({ className, ...props }: ComponentProps<typeof TabsPrimitive.List>) {
  return <TabsPrimitive.List data-slot="tabs-list" className={cn(LIST_CLASS, className)} {...props} />;
}

function TabsTrigger({ className, ...props }: ComponentProps<typeof TabsPrimitive.Trigger>) {
  return <TabsPrimitive.Trigger data-slot="tabs-trigger" className={cn(TRIGGER_CLASS, className)} {...props} />;
}

function TabsContent({ className, ...props }: ComponentProps<typeof TabsPrimitive.Content>) {
  return (
    <TabsPrimitive.Content
      data-slot="tabs-content"
      className={cn('outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring', className)}
      {...props}
    />
  );
}

function NavTabsList({ className, ...props }: ComponentProps<'ul'>) {
  return <ul data-slot="tabs-list" className={cn(LIST_CLASS, className)} {...props} />;
}

function NavTabsTrigger({
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
      className={cn(TRIGGER_CLASS, className)}
      {...props}
    />
  );
}

export { Tabs, TabsList, TabsTrigger, TabsContent, NavTabsList, NavTabsTrigger };
