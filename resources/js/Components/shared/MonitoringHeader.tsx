import { Link } from '@inertiajs/react';
import { Alert, AlertDescription } from '@/Components/ui/alert';

type MonitoringHeaderProps = {
  current: 'chat' | 'asesmen';
};

const TABS = [
  { key: 'chat', label: 'Chat', href: '/admin/monitoring/chat' },
  { key: 'asesmen', label: 'Asesmen', href: '/admin/monitoring/asesmen' },
] as const;

/** Judul, pengantar, banner tanpa identitas, dan tab Monitoring (SCR-022). */
export default function MonitoringHeader({ current }: MonitoringHeaderProps) {
  return (
    <>
      <h1>Monitoring</h1>
      <p className="mt-2 max-w-prose text-text-secondary">
        Tinjauan kualitas, hanya baca. Daftar hanya memuat ID semu, tanggal, dan penanda. Isi baru terlihat setelah
        kamu membukanya, dan setiap pembukaan dicatat.
      </p>

      <Alert className="mt-6">
        <AlertDescription>Isi ditampilkan tanpa identitas. Jangan mencoba mengenali pemiliknya.</AlertDescription>
      </Alert>

      <nav aria-label="Jenis tinjauan" className="mt-6 flex gap-1 border-b border-border">
        {TABS.map(({ key, label, href }) => {
          const active = key === current;
          return (
            <Link
              key={key}
              href={href}
              aria-current={active ? 'page' : undefined}
              className={`inline-flex min-h-11 items-center border-b-2 px-4 transition-colors hover:text-brand-strong ${
                active ? 'border-brand-strong font-medium text-brand-strong' : 'border-transparent text-text-secondary'
              }`}
            >
              {label}
            </Link>
          );
        })}
      </nav>
    </>
  );
}
