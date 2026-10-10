/**
 * Tautan ke Beranda memakai <a> biasa, bukan <Link> Inertia: Beranda adalah
 * halaman Blade, dan kunjungan Inertia ke halaman Blade tampil sebagai modal
 * (Architecture bagian 11). Gambar logo sama dengan Navbar Beranda.
 */
export default function Logo() {
  return (
    <a
      href="/"
      aria-label="RelaxBoss, ke Beranda"
      className="inline-flex min-h-11 items-center gap-2 text-xl font-semibold text-text"
    >
      <img src="/images/logo-relaxboss.png" width={32} height={32} alt="" className="size-8 rounded-lg" />
      RelaxBoss
    </a>
  );
}
