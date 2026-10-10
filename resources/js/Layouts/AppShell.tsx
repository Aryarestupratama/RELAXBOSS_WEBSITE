import type { ReactNode } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
  ClipboardCheck,
  LayoutDashboard,
  LifeBuoy,
  LogOut,
  MessageCircle,
  Smile,
  User,
  type LucideIcon,
} from 'lucide-react';
import HelpPill from '@/Components/shared/HelpPill';
import Logo from '@/Components/shared/Logo';
import LogoutButton from '@/Components/shared/LogoutButton';

type NavItem = {
  label: string;
  href: string;
  icon: LucideIcon;
};

const NAV_ITEMS: NavItem[] = [
  { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
  { label: 'Asesmen', href: '/app/asesmen', icon: ClipboardCheck },
  { label: 'Mood', href: '/app/mood', icon: Smile },
  { label: 'RelaxMate', href: '/app/relaxmate', icon: MessageCircle },
  { label: 'Akun', href: '/app/akun', icon: User },
];

type AppShellProps = {
  title: string;
  /** Tautan bantuan di pojok kanan atas (layar >= 768px). Halaman yang menampilkannya sendiri (Dashboard) mematikannya. */
  showHelp?: boolean;
  children: ReactNode;
};

function isActive(currentUrl: string, href: string): boolean {
  const path = currentUrl.split('?')[0];
  return path === href || path.startsWith(`${href}/`);
}

const railItem =
  'flex h-16 w-full flex-col items-center justify-center gap-1 rounded-xl text-xs transition-colors';

/**
 * Kerangka area pengguna. Layar >= 768px: rail ikon bernavigasi di kiri (navy, melayang).
 * HP: bar atas (logo, bantuan, keluar) dan bar tab di bawah.
 */
export default function AppShell({ title, showHelp = true, children }: AppShellProps) {
  const { url } = usePage();

  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div className="min-h-screen bg-neutral-soft pb-20 md:pb-0 md:pl-30">
        <a
          href="#konten"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover"
        >
          Lewati ke konten
        </a>

        {/* Rail (>= 768px) */}
        <aside className="fixed inset-y-4 left-4 z-40 hidden w-22 flex-col items-center justify-between rounded-2xl bg-primary py-5 shadow-popover md:flex">
          <div className="flex w-full flex-col items-center gap-6 px-2">
            <a href="/" aria-label="RelaxBoss, ke Beranda" className="inline-flex min-h-11 items-center">
              <img src="/images/logo-relaxboss.png" width={40} height={40} alt="" className="size-10 rounded-lg" />
            </a>
            <nav aria-label="Navigasi utama" className="flex w-full flex-col gap-2">
              {NAV_ITEMS.map(({ label, href, icon: Icon }) => {
                const active = isActive(url, href);
                return (
                  <Link
                    key={href}
                    href={href}
                    aria-current={active ? 'page' : undefined}
                    className={`${railItem} ${
                      active
                        ? 'bg-brand text-on-primary'
                        : 'text-on-primary/70 hover:bg-on-primary/10 hover:text-on-primary'
                    }`}
                  >
                    <Icon className="size-5.5" aria-hidden="true" />
                    {label}
                  </Link>
                );
              })}
            </nav>
          </div>
          <div className="w-full px-2">
            <button
              type="button"
              onClick={() => router.post('/keluar')}
              className={`${railItem} h-14 text-on-primary/70 hover:bg-on-primary/10 hover:text-on-primary`}
            >
              <LogOut className="size-5" aria-hidden="true" />
              Keluar
            </button>
          </div>
        </aside>

        {/* Bar atas (HP) */}
        <header className="border-b border-border bg-card md:hidden">
          <div className="flex items-center justify-between gap-3 px-4 py-2">
            <Logo />
            <div className="flex items-center gap-1">
              <a
                href="/konsultasi"
                aria-label="Butuh bantuan sekarang?"
                className="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-crisis-text"
              >
                <LifeBuoy className="size-5" aria-hidden="true" />
              </a>
              <LogoutButton />
            </div>
          </div>
        </header>

        <main
          id="konten"
          className="mx-auto w-full max-w-5xl px-4 py-6 motion-safe:animate-in motion-safe:fade-in motion-safe:duration-500 md:px-8 md:py-8"
        >
          {showHelp ? (
            <div className="mb-4 hidden justify-end md:flex">
              <HelpPill />
            </div>
          ) : null}
          {children}
        </main>

        {/* Bar tab (HP) */}
        <nav
          aria-label="Navigasi bawah"
          className="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card md:hidden"
        >
          <ul className="grid grid-cols-5">
            {NAV_ITEMS.map(({ label, href, icon: Icon }) => {
              const active = isActive(url, href);
              return (
                <li key={href}>
                  <Link
                    href={href}
                    aria-current={active ? 'page' : undefined}
                    className={`flex min-h-14 flex-col items-center justify-center gap-1 text-xs ${
                      active ? 'font-medium text-brand-strong' : 'text-text-secondary'
                    }`}
                  >
                    <Icon className="size-5" aria-hidden="true" />
                    {label}
                  </Link>
                </li>
              );
            })}
          </ul>
        </nav>
      </div>
    </>
  );
}
