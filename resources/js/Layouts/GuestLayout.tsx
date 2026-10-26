import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import AuthShowcase from '@/Components/shared/AuthShowcase';
import AuthTabs from '@/Components/shared/AuthTabs';
import Logo from '@/Components/shared/Logo';
import { useEnter, useFloat } from '@/lib/useMotion';

type Illustration = 'masuk' | 'daftar';

type GuestLayoutProps = {
  title: string;
  /** Ilustrasi panel kiri (hanya tampil di layar lebar). Halaman lain memakai 'masuk'. */
  illustration?: Illustration;
  /** Tab Masuk / Daftar di atas formulir. Kosong untuk Lupa Sandi, Reset Sandi, dan Verifikasi Email. */
  tab?: 'masuk' | 'daftar';
  /** Kolom formulir lebih lebar di layar lebar, untuk formulir panjang (Daftar). */
  wide?: boolean;
  children: ReactNode;
};

const ILLUSTRATIONS: Record<Illustration, { src: string; alt: string }> = {
  masuk: { src: '/images/auth/auth-masuk.webp', alt: '' },
  daftar: { src: '/images/auth/auth-daftar.webp', alt: '' },
};

/**
 * Kerangka halaman tamu (Masuk, Daftar, Lupa Sandi, Reset Sandi, Verifikasi Email).
 * Satu kartu putih melayang di atas `--surface-alt`: panel ilustrasi lavender di kiri (>= 1024px)
 * dan formulir di kanan. HP: panel disembunyikan, logo di atas kartu.
 * Gerak: isi formulir masuk bergantian (`data-m-enter`), maskot melayang pelan; keduanya mati
 * bila pengguna meminta pengurangan gerak.
 */
export default function GuestLayout({ title, illustration = 'masuk', tab, wide = false, children }: GuestLayoutProps) {
  const enter = useEnter<HTMLDivElement>();
  const float = useFloat<HTMLDivElement>();
  const art = ILLUSTRATIONS[illustration];

  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div className="flex min-h-screen items-center justify-center bg-surface-alt px-4 py-6 sm:py-10">
        <div
          className={`grid w-full rounded-3xl border border-border bg-card p-2 shadow-lift motion-safe:animate-in motion-safe:fade-in motion-safe:zoom-in-98 motion-safe:duration-700 lg:min-h-[34rem] lg:max-w-[60rem] lg:grid-cols-[0.9fr_1.1fr] lg:p-2.5 max-lg:max-w-md`}
        >
          <aside className="relative hidden flex-col overflow-hidden rounded-2xl bg-soft-lavender p-6 lg:flex" aria-label="RelaxBoss">
            <span className="pointer-events-none absolute -top-16 -right-14 size-52 rounded-full bg-soft-sky" aria-hidden="true" />
            <span className="pointer-events-none absolute top-1/2 -left-20 size-48 rounded-full bg-soft-lavender" aria-hidden="true" />
            <span className="pointer-events-none absolute right-6 bottom-36 size-16 rounded-full bg-soft-mint" aria-hidden="true" />

            <div className="relative">
              <Logo />
            </div>

            <div className="relative flex flex-1 items-center justify-center py-3">
              <div ref={float}>
                <img
                  src={art.src}
                  width={800}
                  height={800}
                  alt={art.alt}
                  className="w-full max-w-[17rem] motion-safe:animate-in motion-safe:fade-in motion-safe:zoom-in-95 motion-safe:duration-700"
                />
              </div>
            </div>

            <div className="relative">
              <AuthShowcase />
            </div>
          </aside>

          <div className="flex flex-col items-center justify-center px-3 py-6 sm:px-8 lg:px-10 lg:py-6">
            <div className="mb-4 lg:hidden">
              <Logo />
            </div>
            <main id="konten" ref={enter} className={`w-full ${wide ? 'max-w-[26rem]' : 'max-w-[20rem]'}`}>
              {tab ? (
                <div data-m-enter className="mb-5">
                  <AuthTabs active={tab} />
                </div>
              ) : null}
              {children}
            </main>
          </div>
        </div>
      </div>
    </>
  );
}
