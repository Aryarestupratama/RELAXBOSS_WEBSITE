import { Link } from '@inertiajs/react';
import { LogIn, UserPlus } from 'lucide-react';

type AuthTabsProps = {
  active: 'masuk' | 'daftar';
};

const TABS = [
  { key: 'masuk', label: 'Masuk', href: '/masuk', Icon: LogIn },
  { key: 'daftar', label: 'Daftar', href: '/daftar', Icon: UserPlus },
] as const;

/**
 * Pindah antara Masuk dan Daftar. Dua halaman terpisah, jadi ini tautan (bukan tab ARIA):
 * halaman aktif ditandai `aria-current="page"`.
 */
export default function AuthTabs({ active }: AuthTabsProps) {
  return (
    <nav aria-label="Masuk atau daftar" className="flex justify-center">
      <ul className="inline-flex items-center gap-1 rounded-full border border-border bg-neutral-soft p-1">
        {TABS.map(({ key, label, href, Icon }) => {
          const current = key === active;
          return (
            <li key={key}>
              <Link
                href={href}
                aria-current={current ? 'page' : undefined}
                className={`relative inline-flex h-8 items-center gap-1.5 rounded-full px-3.5 text-[0.8125rem] font-medium transition-all duration-300 ease-[cubic-bezier(0.2,0.7,0.2,1)] after:absolute after:inset-x-0 after:-inset-y-1.5 ${
                  current
                    ? 'bg-card text-text shadow-lift'
                    : 'text-text-secondary hover:bg-card/60 hover:text-nav-hover'
                }`}
              >
                <Icon className="size-3.5" aria-hidden="true" />
                {label}
              </Link>
            </li>
          );
        })}
      </ul>
    </nav>
  );
}
