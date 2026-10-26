import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * Table bergaya shadcn. Warna hanya dari token (RULE-050/052). `Table` membungkus `<table>` dalam
 * wadah `overflow-x-auto` sehingga halaman tidak pernah menggulir menyamping. Pakai `TableCaption`
 * (boleh `sr-only`) dan `TableHead scope="col"` agar tabel tetap terbaca pembaca layar.
 */
function Table({ className, ...props }: ComponentProps<'table'>) {
  return (
    <div data-slot="table-container" className="relative w-full overflow-x-auto">
      <table data-slot="table" className={cn('w-full caption-bottom text-left text-sm', className)} {...props} />
    </div>
  );
}

function TableHeader({ className, ...props }: ComponentProps<'thead'>) {
  return <thead data-slot="table-header" className={cn('[&_tr]:border-b', className)} {...props} />;
}

function TableBody({ className, ...props }: ComponentProps<'tbody'>) {
  return <tbody data-slot="table-body" className={cn('[&_tr:last-child]:border-0', className)} {...props} />;
}

function TableFooter({ className, ...props }: ComponentProps<'tfoot'>) {
  return (
    <tfoot
      data-slot="table-footer"
      className={cn('border-t bg-card font-medium [&>tr]:last:border-b-0', className)}
      {...props}
    />
  );
}

function TableRow({ className, ...props }: ComponentProps<'tr'>) {
  return (
    <tr
      data-slot="table-row"
      className={cn('border-b border-border/70 transition-colors hover:bg-soft-lavender/40', className)}
      {...props}
    />
  );
}

function TableHead({ className, scope = 'col', ...props }: ComponentProps<'th'>) {
  return (
    <th
      data-slot="table-head"
      scope={scope}
      className={cn('h-10 px-3 text-xs font-semibold tracking-wide whitespace-nowrap text-text-secondary', className)}
      {...props}
    />
  );
}

function TableCell({ className, ...props }: ComponentProps<'td'>) {
  return <td data-slot="table-cell" className={cn('px-3 py-3 align-middle', className)} {...props} />;
}

function TableCaption({ className, ...props }: ComponentProps<'caption'>) {
  return <caption data-slot="table-caption" className={cn('mt-3 text-sm text-text-secondary', className)} {...props} />;
}

export { Table, TableHeader, TableBody, TableFooter, TableHead, TableRow, TableCell, TableCaption };
