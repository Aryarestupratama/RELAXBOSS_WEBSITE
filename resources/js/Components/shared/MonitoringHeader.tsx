import { Link } from '@inertiajs/react';
import { ClipboardList, Eye, MessagesSquare } from 'lucide-react';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
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
        <InlineNote icon={Eye}>Isi ditampilkan tanpa identitas, jadi jangan mencoba mengenali pemiliknya.</InlineNote>

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
