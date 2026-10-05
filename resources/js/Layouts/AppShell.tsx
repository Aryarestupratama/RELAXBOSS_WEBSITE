import type { ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
  ClipboardCheck,
  LayoutDashboard,
  LifeBuoy,
  MessageCircle,
  Smile,
  User,
  type LucideIcon,
} from 'lucide-react';
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
  children: ReactNode;
};

function isActive(currentUrl: string, href: string): boolean {
  const path = currentUrl.split('?')[0];
  return path === href || path.startsWith(`${href}/`);
}

export default function AppShell({ title, children }: AppShellProps) {
  const { url } = usePage();

  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div className="min-h-screen bg-background pb-20 md:pb-0">
        <a
          href="#konten"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover"
        >
          Lewati ke konten
        </a>

        <header className="border-b border-border bg-card">
          <div className="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-2">
            <Logo />

            <nav aria-label="Navigasi utama" className="hidden items-center gap-1 md:flex">
              {NAV_ITEMS.map(({ label, href }) => {
                const active = isActive(url, href);
                return (
                  <Link
                    key={href}
                    href={href}
                    aria-current={active ? 'page' : undefined}
                    className={`inline-flex min-h-11 items-center rounded-lg px-3 transition-colors hover:text-brand-strong ${
                      active ? 'font-medium text-brand-strong' : 'text-text-secondary'
                    }`}
                  >
                    {label}
                  </Link>
                );
              })}
            </nav>

            <div className="flex items-center gap-1">
              {/* <a> biasa: Konsultasi adalah halaman Blade */}
              <a
                href="/konsultasi"
                aria-label="Butuh bantuan sekarang?"
                className="inline-flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-lg px-2 text-sm font-medium text-brand-strong"
              >
                <LifeBuoy className="size-5" aria-hidden="true" />
                <span className="hidden sm:inline">Butuh bantuan sekarang?</span>
              </a>
              <LogoutButton />
            </div>
          </div>
        </header>

        <main id="konten" className="mx-auto w-full max-w-5xl px-4 py-6 md:py-8">
          {children}
        </main>

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
