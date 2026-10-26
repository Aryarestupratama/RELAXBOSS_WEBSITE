import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Separator } from '@/Components/ui/separator';
import { cn } from '@/lib/utils';

type PageHeaderProps = {
  /** Satu `h1` per halaman. */
  title: ReactNode;
  description?: ReactNode;
  /** Tautan kembali ke halaman induk (di atas judul). */
  back?: { href: string; label: string };
  /** Aksi di kanan judul (tombol pil `outline`, maks. satu yang menonjol). */
  actions?: ReactNode;
  className?: string;
};

/**
 * Kepala halaman area pengguna (`/app/*`, Dashboard): tautan kembali (opsional), `h1`, pengantar, aksi,
 * lalu `Separator` ke isi. Satu bentuk untuk semua halaman agar konsisten. Masuk bergantian lewat `data-load`;
 * jarak ke isi (`mb-5`) sudah termasuk, jadi isi tidak perlu `mt-*`.
 */
export default function PageHeader({ title, description, back, actions, className }: PageHeaderProps) {
  return (
    <header data-load className={cn('mb-5', className)}>
      {back ? (
        <Link
          href={back.href}
          className="group inline-flex min-h-11 items-center gap-2 text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-nav-hover"
        >
          <ArrowLeft className="size-4 transition-transform duration-200 group-hover:-translate-x-0.5" aria-hidden="true" />
          {back.label}
        </Link>
      ) : null}
      <div className="flex flex-wrap items-end justify-between gap-x-4 gap-y-3">
        <div className="min-w-0">
          <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">{title}</h1>
          {description ? <p className="mt-1 max-w-prose text-sm text-text-secondary">{description}</p> : null}
        </div>
        {actions}
      </div>
      <Separator className="mt-4" />
    </header>
  );
}
