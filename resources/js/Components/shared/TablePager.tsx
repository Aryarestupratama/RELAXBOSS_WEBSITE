import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { ROW_BUTTON } from '@/lib/adminTable';
import { cn } from '@/lib/utils';

type TablePagerProps = {
  label: string;
  prevUrl: string | null;
  nextUrl: string | null;
  className?: string;
};

/** Paginasi sederhana (Sebelumnya, Berikutnya). Tidak tampil bila hanya ada satu halaman. */
export default function TablePager({ label, prevUrl, nextUrl, className }: TablePagerProps) {
  if (!prevUrl && !nextUrl) {
    return null;
  }

  return (
    <nav aria-label={label} className={cn('flex justify-between gap-3', className)}>
      {prevUrl ? (
        <Button asChild variant="outline" className={ROW_BUTTON}>
          <Link href={prevUrl}>
            <ChevronLeft aria-hidden="true" />
            Sebelumnya
          </Link>
        </Button>
      ) : (
        <span />
      )}
      {nextUrl ? (
        <Button asChild variant="outline" className={ROW_BUTTON}>
          <Link href={nextUrl}>
            Berikutnya
            <ChevronRight aria-hidden="true" />
          </Link>
        </Button>
      ) : null}
    </nav>
  );
}
