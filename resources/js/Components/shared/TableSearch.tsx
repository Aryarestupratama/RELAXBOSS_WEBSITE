import { Link } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { ROW_BUTTON } from '@/lib/adminTable';

type TableSearchProps = {
  id: string;
  /** Label untuk pembaca layar dan placeholder, mis. "Cari email". */
  label: string;
  value: string;
  onChange: (value: string) => void;
  onSubmit: () => void;
  /** Tautan untuk menghapus pencarian; tampil hanya bila ada pencarian yang sedang berlaku. */
  clearHref?: string;
  maxLength?: number;
};

/** Pencarian tabel: kolom ramping (maks. 24rem), dikirim lewat Enter atau tombol Cari. */
export default function TableSearch({ id, label, value, onChange, onSubmit, clearHref, maxLength = 100 }: TableSearchProps) {
  return (
    <form
      role="search"
      onSubmit={(event) => {
        event.preventDefault();
        onSubmit();
      }}
      className="flex min-w-0 flex-1 flex-wrap items-center gap-2"
    >
      <div className="relative min-w-0 flex-1 basis-56 sm:max-w-sm">
        <Label htmlFor={id} className="sr-only">
          {label}
        </Label>
        <Search
          className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-text-secondary"
          aria-hidden="true"
        />
        <Input
          id={id}
          type="search"
          name="q"
          maxLength={maxLength}
          autoComplete="off"
          placeholder={label}
          value={value}
          onChange={(event) => onChange(event.target.value)}
          className="rounded-full pl-10 md:h-9 md:min-h-9 md:py-1 md:text-sm"
        />
      </div>
      <Button type="submit" className={ROW_BUTTON}>
        Cari
      </Button>
      {clearHref ? (
        <Button asChild variant="ghost" className={ROW_BUTTON}>
          <Link href={clearHref}>
            <X aria-hidden="true" />
            Hapus pencarian
          </Link>
        </Button>
      ) : null}
    </form>
  );
}
