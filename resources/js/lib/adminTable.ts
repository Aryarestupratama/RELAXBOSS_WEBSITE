/**
 * Kelas tombol di dalam tabel, bilah saring, dan pager admin: pil penuh dengan target sentuh 44px di HP (kartu),
 * lalu memadat menjadi 36px di layar >= 768px agar baris tabel desktop tidak terlalu tinggi.
 */
export const ROW_BUTTON = 'rounded-full! px-5 text-sm md:min-h-9 md:px-4 md:py-1.5';

/** Tampilan kartu di HP untuk blok yang di desktop menyatu dengan kartu induk (`DataTableCard`). */
export const MOBILE_CARD = 'max-md:rounded-3xl max-md:border max-md:border-border/60 max-md:bg-card max-md:shadow-lift';
