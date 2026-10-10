import type { ReactNode } from 'react';
import { Head } from '@inertiajs/react';
import Logo from '@/Components/shared/Logo';

type Illustration = 'masuk' | 'daftar';

type GuestLayoutProps = {
  title: string;
  /** Ilustrasi panel kiri (hanya tampil di layar lebar). Halaman lain memakai 'masuk'. */
  illustration?: Illustration;
  /** Kartu lebih lebar di layar lebar, untuk formulir panjang (Daftar) agar muat satu layar. */
  wide?: boolean;
  children: ReactNode;
};

const ILLUSTRATIONS: Record<Illustration, string> = {
  masuk: '/images/auth/auth-masuk.webp',
  daftar: '/images/auth/auth-daftar.webp',
};

/**
 * Kerangka halaman tamu (Masuk, Daftar, Lupa Sandi, Reset Sandi, Verifikasi Email).
 * Layar lebar: panel ilustrasi krem di kiri (menempel) dan kartu formulir di kanan.
 * HP: panel disembunyikan, logo di atas kartu.
 */
export default function GuestLayout({ title, illustration = 'masuk', wide = false, children }: GuestLayoutProps) {
  return (
    <>
      <Head title={`${title} | RelaxBoss`} />
      <div className="min-h-screen lg:grid lg:grid-cols-[45fr_55fr]">
        <aside className="hidden bg-neutral-soft p-10 lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
          <div>
            <Logo />
          </div>
          <div className="flex flex-1 items-center justify-center">
            <img
              src={ILLUSTRATIONS[illustration]}
              width={800}
              height={800}
              alt=""
              className="w-full max-w-md motion-safe:animate-in motion-safe:fade-in motion-safe:zoom-in-95 motion-safe:duration-700"
            />
          </div>
        </aside>

        <div className="flex min-h-screen flex-col items-center justify-center px-4 py-8 sm:py-12 lg:px-10 lg:py-4">
          <div className="mb-6 lg:hidden">
            <Logo />
          </div>
          <main
            id="konten"
            className={`w-full max-w-md rounded-xl border border-border bg-card p-6 shadow-none motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-500 sm:p-8 lg:shadow-card ${wide ? 'lg:max-w-2xl lg:p-6' : 'lg:max-w-[460px]'}`}
          >
            {children}
          </main>
        </div>
      </div>
    </>
  );
}
