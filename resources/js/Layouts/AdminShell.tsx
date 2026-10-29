import { useEffect, type ReactNode } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
  Activity,
  ArrowLeft,
  BookOpen,
  ClipboardCheck,
  LayoutDashboard,
  LogOut,
  Megaphone,
  Users,
  type LucideIcon,
} from 'lucide-react';
import Logo from '@/Components/shared/Logo';
import LogoutButton from '@/Components/shared/LogoutButton';
import Toaster from '@/Components/shared/Toaster';
import { Badge } from '@/Components/ui/badge';
import { Card } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { initMotion } from '@/lib/motion';

type NavItem = {
  label: string;
  href: string;
  icon: LucideIcon;
};

const NAV_ITEMS: NavItem[] = [
  { label: 'Ringkasan', href: '/admin', icon: LayoutDashboard },
  { label: 'Asesmen', href: '/admin/asesmen', icon: ClipboardCheck },
  { label: 'Artikel', href: '/admin/artikel', icon: BookOpen },
  { label: 'Info', href: '/admin/pengumuman', icon: Megaphone },
  { label: 'Pengguna', href: '/admin/pengguna', icon: Users },
  { label: 'Monitoring', href: '/admin/monitoring', icon: Activity },
];

type AdminShellProps = {
  title: string;
  /** Halaman mengatur gerak masuknya sendiri lewat penanda data-load; bila tidak, seluruh konten masuk sekaligus. */
  staggered?: boolean;
  children: ReactNode;
};

function isActive(currentUrl: string, href: string): boolean {
  const path = currentUrl.split('?')[0];
  return href === '/admin' ? path === href : path === href || path.startsWith(`${href}/`);
}

/* Satu kurva gerak dengan AppShell dan motion.ts. */
const EASE = 'ease-[cubic-bezier(0.2,0.7,0.2,1)]';

/* Item rail: pola yang sama dengan AppShell (hover pil --primary, aktif --soft-lavender dengan teks --text). */
const railItem =
  `group relative isolate flex h-14 w-full flex-col items-center justify-center gap-1 rounded-xl px-1 text-[0.6875rem] font-medium text-primary transition-all duration-300 ${EASE} ` +
  'before:absolute before:inset-0 before:-z-10 before:scale-75 before:rounded-xl before:bg-primary before:opacity-0 before:transition-all before:duration-300 before:ease-[cubic-bezier(0.2,0.7,0.2,1)] ' +
  'hover:text-on-primary hover:before:scale-100 hover:before:opacity-100 ' +
  'aria-[current=page]:bg-soft-lavender aria-[current=page]:text-text aria-[current=page]:shadow-lift aria-[current=page]:before:opacity-0';

const iconMotion = `size-5 transition-transform duration-300 ${EASE} group-hover:-translate-y-0.5 group-hover:scale-110 group-aria-[current=page]:scale-110`;

/**
 * Kerangka area admin (SCR-019 dst.). Bukan pengganti pemeriksaan server: middleware `admin` menjaga route.
 * Layar >= 768px: rail ikon di kiri dalam `Card` (pola sama dengan `AppShell`). HP: bar atas dan bar tab di bawah.
 */
export default function AdminShell({ title, staggered = false, children }: AdminShellProps) {
  const { url } = usePage();

  useEffect(() => {
    initMotion();
  }, [url]);

  return (
    <>
      <Head title={`${title} | Admin RelaxBoss`} />
      <Toaster />
      <div className="min-h-screen bg-background pb-16 md:pb-0 md:pl-28">
        <a
          href="#konten"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover"
        >
          Lewati ke konten
        </a>

        {/* Rail (>= 768px) */}
        <aside className="fixed inset-y-3 left-3 z-40 hidden w-24 md:block">
          <Card className="h-full justify-between rounded-2xl border-[1.5px] border-primary py-5">
            <div className="flex w-full flex-col items-center gap-5 px-2.5">
              <div className="flex flex-col items-center gap-1.5">
                <a href="/" aria-label="RelaxBoss, ke Beranda" data-press className="inline-flex min-h-11 items-center">
                  <img src="/images/logo-relaxboss.png" width={36} height={36} alt="" className="size-9 rounded-lg" />
                </a>
                <Badge variant="lavender">Admin</Badge>
              </div>
              <Separator className="w-10" />
              <nav aria-label="Navigasi admin" className="flex w-full flex-col gap-2">
                {NAV_ITEMS.map(({ label, href, icon: Icon }) => (
                  <Link
                    key={href}
                    href={href}
                    data-press
                    aria-current={isActive(url, href) ? 'page' : undefined}
                    className={railItem}
                  >
                    <Icon className={iconMotion} aria-hidden="true" />
                    {label}
                  </Link>
                ))}
              </nav>
            </div>
            <div className="flex w-full flex-col items-center gap-2 px-2.5">
              <Separator className="mb-1 w-10" />
              {/* Dashboard pengguna adalah halaman Inertia lain; tautan biasa sudah cukup */}
              <Link href="/dashboard" data-press className={railItem}>
                <ArrowLeft className={iconMotion} aria-hidden="true" />
                Aplikasi
              </Link>
              <button type="button" data-press onClick={() => router.post('/keluar')} className={railItem}>
                <LogOut className={iconMotion} aria-hidden="true" />
                Keluar
              </button>
            </div>
          </Card>
        </aside>

        {/* Bar atas (HP) */}
        <header className="border-b border-border bg-card md:hidden">
          <div className="flex items-center justify-between gap-2 px-3 py-1.5 min-[26rem]:gap-3 min-[26rem]:px-4">
            <div className="flex min-w-0 items-center gap-2">
              <Logo compact />
              <Badge variant="lavender">Admin</Badge>
            </div>
            <div className="flex items-center gap-1">
              <Link
                href="/dashboard"
                className="inline-flex min-h-11 items-center rounded-lg px-2 text-sm font-medium text-primary"
              >
                Aplikasi
              </Link>
              <LogoutButton className="px-2" />
            </div>
          </div>
        </header>

        <main id="konten" className="mx-auto w-full max-w-7xl px-4 py-5 md:px-6 md:py-7">
          <div data-load={staggered ? undefined : ''}>
            <p className="mb-3 hidden text-[0.8125rem] font-medium text-text-secondary md:block">RelaxBoss / Admin</p>
            {children}
          </div>
        </main>

        {/* Bar tab (HP) */}
        <nav
          aria-label="Navigasi admin bawah"
          className="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/90 backdrop-blur md:hidden"
        >
          <ul className="grid grid-cols-6">
            {NAV_ITEMS.map(({ label, href, icon: Icon }) => {
              const active = isActive(url, href);
              return (
                <li key={href}>
                  <Link
                    href={href}
                    data-press
                    aria-current={active ? 'page' : undefined}
                    className={`group flex min-h-14 flex-col items-center justify-center gap-0.5 text-[0.625rem] font-medium tracking-tight transition-colors min-[360px]:text-[0.6875rem] duration-300 ${EASE} ${
                      active ? 'text-primary' : 'text-text-secondary hover:text-nav-hover'
                    }`}
                  >
                    <span
                      className={`flex h-6 w-11 items-center justify-center rounded-full transition-all duration-300 ${EASE} ${
                        active ? 'bg-brand-soft/30' : 'group-hover:bg-nav-hover/[0.07]'
                      }`}
                    >
                      <Icon className="size-[1.125rem]" aria-hidden="true" />
                    </span>
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
