import { Link } from '@inertiajs/react';
import { ClipboardList, Eye, MessagesSquare } from 'lucide-react';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { NavTabsList, NavTabsTrigger } from '@/Components/ui/tabs';

type MonitoringHeaderProps = {
  current: 'chat' | 'asesmen';
  /** Tautan kembali ke daftar (halaman detail). */
  back?: { href: string; label: string };
};

const TABS = [
  { key: 'chat', label: 'Chat', href: '/admin/monitoring/chat', Icon: MessagesSquare },
  { key: 'asesmen', label: 'Asesmen', href: '/admin/monitoring/asesmen', Icon: ClipboardList },
] as const;

/** Judul, pengantar, catatan tanpa identitas, dan tab Monitoring (SCR-022). Satu-satunya `h1` halaman. */
export default function MonitoringHeader({ current, back }: MonitoringHeaderProps) {
  return (
    <>
      <PageHeader
        title="Monitoring"
        description="Tinjauan kualitas, hanya baca. Daftar hanya memuat ID semu, tanggal, dan penanda. Isi baru terlihat setelah kamu membukanya, dan setiap pembukaan dicatat."
        back={back}
      />

      <div data-load className="mb-6 flex flex-col gap-4">
        <Alert
          role="note"
          className="rounded-2xl border-transparent border-l-4 border-l-brand-strong px-4 py-3 shadow-card"
        >
          <Eye className="text-brand-strong!" aria-hidden="true" />
          <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">
            Isi ditampilkan tanpa identitas. Jangan mencoba mengenali pemiliknya.
          </AlertDescription>
        </Alert>

        <nav aria-label="Jenis tinjauan">
          <NavTabsList>
            {TABS.map(({ key, label, href, Icon }) => (
              <li key={key}>
                <NavTabsTrigger asChild active={key === current}>
                  <Link href={href}>
                    <Icon className="size-3.5" aria-hidden="true" />
                    {label}
                  </Link>
                </NavTabsTrigger>
              </li>
            ))}
          </NavTabsList>
        </nav>
      </div>
    </>
  );
}
