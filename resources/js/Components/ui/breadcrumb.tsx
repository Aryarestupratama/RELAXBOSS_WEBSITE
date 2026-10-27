import type { ComponentProps } from 'react';
import { ChevronRight } from 'lucide-react';
import { Slot } from 'radix-ui';
import { cn } from '@/lib/utils';

/**
 * Breadcrumb gaya shadcn. Tautan memakai `asChild` dengan `Link` Inertia (atau `<a>` untuk halaman Blade).
 * Warna hanya dari token; target sentuh tautan minimal 44px (RULE-055).
 */
function Breadcrumb({ ...props }: ComponentProps<'nav'>) {
  return <nav aria-label="Jejak halaman" data-slot="breadcrumb" {...props} />;
}

function BreadcrumbList({ className, ...props }: ComponentProps<'ol'>) {
  return (
    <ol
      data-slot="breadcrumb-list"
      className={cn('flex flex-wrap items-center gap-x-1 text-[0.8125rem] text-text-secondary', className)}
      {...props}
    />
  );
}

function BreadcrumbItem({ className, ...props }: ComponentProps<'li'>) {
  return <li data-slot="breadcrumb-item" className={cn('inline-flex min-w-0 items-center gap-1', className)} {...props} />;
}

function BreadcrumbLink({
  asChild = false,
  className,
  ...props
}: ComponentProps<'a'> & { asChild?: boolean }) {
  const Comp = asChild ? Slot.Root : 'a';
  return (
    <Comp
      data-slot="breadcrumb-link"
      className={cn('inline-flex min-h-11 items-center rounded-sm font-medium transition-colors hover:text-primary', className)}
      {...props}
    />
  );
}

function BreadcrumbPage({ className, ...props }: ComponentProps<'span'>) {
  return (
    <span
      data-slot="breadcrumb-page"
      aria-current="page"
      className={cn('block truncate font-semibold text-text', className)}
      {...props}
    />
  );
}

function BreadcrumbSeparator({ className, ...props }: ComponentProps<'li'>) {
  return (
    <li
      data-slot="breadcrumb-separator"
      role="presentation"
      aria-hidden="true"
      className={cn('text-text-muted [&>svg]:size-3.5', className)}
      {...props}
    >
      <ChevronRight />
    </li>
  );
}

export { Breadcrumb, BreadcrumbList, BreadcrumbItem, BreadcrumbLink, BreadcrumbPage, BreadcrumbSeparator };
