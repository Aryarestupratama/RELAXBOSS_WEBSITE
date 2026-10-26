import { Link } from '@inertiajs/react';
import { LogIn, UserPlus } from 'lucide-react';
import { TabsList, TabsTrigger } from '@/Components/ui/tabs';

type AuthTabsProps = {
  active: 'masuk' | 'daftar';
};

const TABS = [
  { key: 'masuk', label: 'Masuk', href: '/masuk', Icon: LogIn },
  { key: 'daftar', label: 'Daftar', href: '/daftar', Icon: UserPlus },
] as const;

/**
 * Pindah antara Masuk dan Daftar dengan gaya shadcn Tabs. Dua halaman terpisah, jadi ini tautan
 * (bukan tab ARIA): halaman aktif ditandai `aria-current="page"`.
 */
export default function AuthTabs({ active }: AuthTabsProps) {
  return (
    <nav aria-label="Masuk atau daftar" className="flex justify-center">
      <TabsList>
        {TABS.map(({ key, label, href, Icon }) => (
          <li key={key}>
            <TabsTrigger asChild active={key === active}>
              <Link href={href}>
                <Icon className="size-3.5" aria-hidden="true" />
                {label}
              </Link>
            </TabsTrigger>
          </li>
        ))}
      </TabsList>
    </nav>
  );
}
