import { Leaf } from 'lucide-react';

/**
 * Tautan ke Beranda memakai <a> biasa, bukan <Link> Inertia: Beranda adalah
 * halaman Blade, dan kunjungan Inertia ke halaman Blade tampil sebagai modal
 * (Architecture bagian 11).
 */
export default function Logo() {
  return (
    <a
      href="/"
      aria-label="RelaxBoss, ke Beranda"
      className="inline-flex min-h-11 items-center gap-2 text-xl font-semibold text-text"
    >
      <Leaf className="size-6 text-brand-strong" aria-hidden="true" />
      RelaxBoss
    </a>
  );
}
