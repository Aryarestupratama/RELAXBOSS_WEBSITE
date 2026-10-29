import { Link } from '@inertiajs/react';
import { ListFilter } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { ROW_BUTTON } from '@/lib/adminTable';

type TableFilterTabsProps<K extends string> = {
  /** Nama bagian untuk pembaca layar, mis. "Saringan Percakapan". */
  label: string;
  basePath: string;
  options: { key: K; label: string }[];
  current: K;
};

/** Saringan tabel berupa pil tautan (`?saring=`); halaman aktif ditandai `aria-current`. Ditaruh di `TableToolbar`. */
export default function TableFilterTabs<K extends string>({ label, basePath, options, current }: TableFilterTabsProps<K>) {
  return (
    <nav aria-label={label} className="flex flex-wrap items-center gap-2">
      <span className="mr-1 inline-flex items-center gap-1.5 text-[0.8125rem] font-medium text-text-secondary">
        <ListFilter className="size-4" aria-hidden="true" />
        Saring
      </span>
      {options.map(({ key, label: optionLabel }) => (
        <Button key={key} asChild variant={key === current ? 'default' : 'outline'} className={ROW_BUTTON}>
          <Link href={`${basePath}?saring=${key}`} aria-current={key === current ? 'true' : undefined}>
            {optionLabel}
          </Link>
        </Button>
      ))}
    </nav>
  );
}
