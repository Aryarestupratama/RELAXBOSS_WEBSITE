import type { ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import Logo from '@/Components/shared/Logo';
import LogoutButton from '@/Components/shared/LogoutButton';

type NavItem = {
  label: string;
  href: string;
  /** Halaman belum dibuat: tampil sebagai teks nonaktif, bukan tautan (tautan akan 404). */
  soon?: boolean;
};

// Asesmen dibuka di TASK-024; Pengguna dan Monitoring dibuka satu per satu di TASK-025 dan TASK-026.
const NAV_ITEMS: NavItem[] = [
  { label: 'Ringkasan', href: '/admin' },
  { label: 'Asesmen', href: '/admin/asesmen' },
  { label: 'Pengguna', href: '/admin/pengguna', soon: true },
  { label: 'Monitoring', href: '/admin/monitoring', soon: true },
];

type AdminShellProps = {
  title: string;
  children: ReactNode;
};

function isActive(currentUrl: string, href: string): boolean {
  const path = currentUrl.split('?')[0];
  return href === '/admin' ? path === href : path === href || path.startsWith(`${href}/`);
}

/** Kerangka area admin (SCR-019 dst.). Bukan pengganti pemeriksaan server: middleware `admin` menjaga route. */
export default function AdminShell({ title, children }: AdminShellProps) {
  const { url } = usePage();

  return (
    <>
      <Head title={`${title} | Admin RelaxBoss`} />
      <div className="min-h-screen bg-background">
        <a
          href="#konten"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover"
        >
          Lewati ke konten
        </a>

        <header className="border-b border-border bg-card">
          <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-1 px-4 py-2">
            <div className="flex items-center gap-3">
              <Logo />
              <span className="rounded-md bg-neutral-soft px-2 py-1 text-sm font-medium text-brand-strong">Admin</span>
            </div>

            <div className="flex items-center gap-1">
              {/* Dashboard pengguna adalah halaman Inertia lain; tautan biasa sudah cukup */}
              <Link
                href="/dashboard"
                className="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-brand-strong"
              >
                Ke aplikasi
              </Link>
              <LogoutButton />
            </div>
          </div>

          <nav aria-label="Navigasi admin" className="mx-auto flex max-w-6xl flex-wrap gap-1 px-4 pb-2">
            {NAV_ITEMS.map(({ label, href, soon }) => {
              if (soon) {
                return (
                  <span
                    key={href}
                    aria-disabled="true"
                    className="inline-flex min-h-11 items-center rounded-lg px-3 text-text-secondary opacity-70"
                  >
                    {label} <span className="ml-1 text-xs">(segera)</span>
                  </span>
                );
              }
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
        </header>

        <main id="konten" className="mx-auto w-full max-w-6xl px-4 py-6 md:py-8">
          {children}
        </main>
      </div>
    </>
  );
}
