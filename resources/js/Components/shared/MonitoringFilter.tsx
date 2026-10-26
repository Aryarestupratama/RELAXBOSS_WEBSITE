import { Link } from '@inertiajs/react';
import { ListFilter } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';

type MonitoringFilterProps<K extends string> = {
  /** Nama bagian untuk pembaca layar, mis. "Saringan Percakapan". */
  label: string;
  basePath: string;
  options: { key: K; label: string }[];
  current: K;
};

/** Bilah saringan Monitoring: satu `Card`, pil tautan (halaman aktif ditandai `aria-current`). */
export default function MonitoringFilter<K extends string>({ label, basePath, options, current }: MonitoringFilterProps<K>) {
  return (
    <Card data-load className="p-3 md:p-4">
      <nav aria-label={label} className="flex flex-wrap items-center gap-2">
        <span className="mr-1 inline-flex items-center gap-1.5 text-[0.8125rem] font-medium text-text-secondary">
          <ListFilter className="size-4" aria-hidden="true" />
          Saring
        </span>
        {options.map(({ key, label: optionLabel }) => (
          <Button
            key={key}
            asChild
            variant={key === current ? 'default' : 'outline'}
            className="rounded-full! px-5 text-sm"
          >
            <Link href={`${basePath}?saring=${key}`} aria-current={key === current ? 'true' : undefined}>
              {optionLabel}
            </Link>
          </Button>
        ))}
      </nav>
    </Card>
  );
}
