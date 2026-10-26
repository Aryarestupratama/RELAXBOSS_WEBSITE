import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/Components/ui/button';

type MonitoringPagerProps = {
  label: string;
  prevUrl: string | null;
  nextUrl: string | null;
};

const PILL = 'rounded-full! px-5 text-sm';

/** Paginasi sederhana (Sebelumnya, Berikutnya) untuk daftar Monitoring. Tidak tampil bila hanya satu halaman. */
export default function MonitoringPager({ label, prevUrl, nextUrl }: MonitoringPagerProps) {
  if (!prevUrl && !nextUrl) {
    return null;
  }

  return (
    <nav aria-label={label} data-load className="flex justify-between gap-3">
      {prevUrl ? (
        <Button asChild variant="outline" className={PILL}>
          <Link href={prevUrl}>
            <ChevronLeft aria-hidden="true" />
            Sebelumnya
          </Link>
        </Button>
      ) : (
        <span />
      )}
      {nextUrl ? (
        <Button asChild variant="outline" className={PILL}>
          <Link href={nextUrl}>
            Berikutnya
            <ChevronRight aria-hidden="true" />
          </Link>
        </Button>
      ) : null}
    </nav>
  );
}
