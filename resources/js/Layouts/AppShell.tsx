import { useEffect, type ReactNode } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
  ClipboardCheck,
  LayoutDashboard,
  LifeBuoy,
  LogOut,
  MessageCircle,
  ShieldCheck,
  Smile,
  User,
  type LucideIcon,
} from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { initMotion } from '@/lib/motion';

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
  /** Tombol bantuan di navbar (layar >= 768px). Halaman yang menampilkannya sendiri (Dashboard) mematikannya. */
  showHelp?: boolean;
  /** Konten lebih lebar (Dashboard). Halaman lain tetap sempit agar enak dibaca. */
  wide?: boolean;
  /** Halaman mengatur gerak masuknya sendiri lewat penanda data-load; bila tidak, seluruh konten masuk sekaligus. */
  staggered?: boolean;
  /**
   * Halaman setinggi layar tanpa gulir halaman (RelaxMate Percakapan): konten mengisi sisa tinggi dan yang
   * boleh digulir hanya panel di dalamnya. Bila layar lebih pendek dari 36rem, halaman tetap bisa digulir.
   * Akar `relative` wajib: tanpa itu elemen `sr-only` (absolut) di tiap ChatBubble lolos dari `overflow-hidden`
   * dan memanjangkan dokumen sehingga halaman bisa digulir pada percakapan panjang.
   */
  fill?: boolean;
  children: ReactNode;
};

function isActive(currentUrl: string, href: string): boolean {
  const path = currentUrl.split('?')[0];
  return path === href || path.startsWith(`${href}/`);
}

/* Satu kurva gerak dengan navbar publik dan motion.ts: [0.2, 0.7, 0.2, 1]. */
const EASE = 'ease-[cubic-bezier(0.2,0.7,0.2,1)]';

/*
 * Item rail (putih, garis --primary): teks dan ikon --primary; hover = pil --primary membesar dari tengah
 * dengan teks --on-primary; halaman aktif = --brand dengan teks --on-primary (RULE-052) dan --shadow-lift.
 */
const railItem =
  `group relative isolate flex h-14 w-full flex-col items-center justify-center gap-1 rounded-xl px-1 text-[0.6875rem] font-medium text-primary transition-all duration-300 ${EASE} ` +
  'before:absolute before:inset-0 before:-z-10 before:scale-75 before:rounded-xl before:bg-primary before:opacity-0 before:transition-all before:duration-300 before:ease-[cubic-bezier(0.2,0.7,0.2,1)] ' +
  'hover:text-on-primary hover:before:scale-100 hover:before:opacity-100 ' +
  'aria-[current=page]:bg-brand aria-[current=page]:text-on-primary aria-[current=page]:shadow-lift aria-[current=page]:before:opacity-0';

const iconMotion = `size-5 transition-transform duration-300 ${EASE} group-hover:-translate-y-0.5 group-hover:scale-110 group-aria-[current=page]:scale-110`;

/**
 * Kerangka area pengguna. Layar >= 768px: rail ikon ringkas di kiri (navy, melayang) dengan hover pil dan penanda aktif.
 * HP: bar atas ringkas dan bar tab di bawah.
 */
export default function AppShell({
  title,
  showHelp = true,
  wide = false,
  staggered = false,
  fill = false,
  children,
}: AppShellProps) {
  const { url, props } = usePage();
  const isAdmin = (props as { auth?: { is_admin: boolean } | null }).auth?.is_admin === true;

  useEffect(() => {
    initMotion();
  }, [url]);

  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div
        className={`bg-neutral-soft pb-16 md:pb-0 md:pl-28 ${
          fill ? 'relative flex h-dvh min-h-144 flex-col overflow-hidden' : 'min-h-screen'
        }`}
      >
        <a
          href="#konten"
          className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover"
        >
          Lewati ke konten
        </a>

        {/* Rail (>= 768px) */}
        <aside className="fixed inset-y-3 left-3 z-40 hidden w-24 md:block">
          <Card className="h-full justify-between overflow-y-auto rounded-2xl border-[1.5px] border-primary py-5">
            <div className="flex w-full flex-col items-center gap-5 px-2.5">
              <a href="/" aria-label="RelaxBoss, ke Beranda" data-press className="inline-flex min-h-11 items-center">
                <img src="/images/logo-relaxboss.png" width={36} height={36} alt="" className="size-9 rounded-lg" />
              </a>
              <Separator className="w-10" />
              <nav aria-label="Navigasi utama" className="flex w-full flex-col gap-2">
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
              {isAdmin ? (
                <Link href="/admin" data-press className={railItem}>
                  <ShieldCheck className={iconMotion} aria-hidden="true" />
                  Admin
                </Link>
              ) : null}
              {showHelp ? (
                <a href="/konsultasi" data-press className={railItem}>
                  <LifeBuoy className={iconMotion} aria-hidden="true" />
                  Bantuan
                </a>
              ) : null}
              <button type="button" data-press onClick={() => router.post('/keluar')} className={railItem}>
                <LogOut className={iconMotion} aria-hidden="true" />
                Keluar
              </button>
            </div>
          </Card>
        </aside>

        {/* Bar atas (HP) */}
        <header className="shrink-0 border-b border-border bg-card md:hidden">
          <div className="flex items-center justify-between gap-3 px-4 py-1.5">
            <a
              href="/"
              aria-label="RelaxBoss, ke Beranda"
              className="inline-flex min-h-11 items-center gap-2 text-base font-semibold text-text"
            >
              <img src="/images/logo-relaxboss.png" width={28} height={28} alt="" className="size-7 rounded-lg" />
              RelaxBoss
            </a>
            <div className="flex items-center gap-1">
              {isAdmin ? (
                <Link
                  href="/admin"
                  aria-label="Ke Panel Admin"
                  className="inline-flex size-11 items-center justify-center rounded-lg text-primary transition-colors hover:text-nav-hover"
                >
                  <ShieldCheck className="size-5" aria-hidden="true" />
                </Link>
              ) : null}
              <a
                href="/konsultasi"
                aria-label="Butuh bantuan sekarang?"
                className="inline-flex size-11 items-center justify-center rounded-lg text-crisis-text"
              >
                <LifeBuoy className="size-5" aria-hidden="true" />
              </a>
              <Button
                type="button"
                variant="outline"
                data-press
                onClick={() => router.post('/keluar')}
                className="px-3 py-0 text-sm [&_svg:not([class*='size-'])]:size-4"
              >
                <LogOut aria-hidden="true" />
                Keluar
              </Button>
            </div>
          </div>
        </header>

        <main
          id="konten"
          className={`mx-auto w-full px-4 py-5 md:px-6 md:py-7 ${wide ? 'max-w-7xl' : 'max-w-4xl'} ${
            fill ? 'flex min-h-0 flex-1 flex-col' : ''
          } [&_h1:not([class])]:text-2xl md:[&_h1:not([class])]:text-[1.75rem] [&_h2:not([class])]:text-lg`}
        >
          <div data-load={staggered ? undefined : ''} className={fill ? 'flex min-h-0 flex-1 flex-col' : undefined}>
            {children}
          </div>
        </main>

        {/* Bar tab (HP) */}
        <nav
          aria-label="Navigasi bawah"
          className="fixed inset-x-0 bottom-0 z-40 border-t border-border bg-card/90 backdrop-blur md:hidden"
        >
          <ul className="grid grid-cols-5">
            {NAV_ITEMS.map(({ label, href, icon: Icon }) => {
              const active = isActive(url, href);
              return (
                <li key={href}>
                  <Link
                    href={href}
                    data-press
                    aria-current={active ? 'page' : undefined}
                    className={`group flex min-h-14 flex-col items-center justify-center gap-0.5 text-[0.6875rem] font-medium transition-colors duration-300 ${EASE} ${
                      active ? 'text-brand-strong' : 'text-text-secondary hover:text-nav-hover'
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
