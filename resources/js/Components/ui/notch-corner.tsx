import type { ReactNode } from 'react';

/**
 * Takik sudut kanan bawah untuk kartu fitur: sudut kartu "digigit" melengkung, di dalamnya ada cakram panah.
 * Warna takik harus sama dengan permukaan di belakang kartu (di sini --background), jadi takik tampak sebagai
 * lubang. Lengkung takik sepusat dengan cakram; pertemuan takik dan tepi kartu dihaluskan dengan dua fillet.
 * Pasang di dalam elemen `relative group`. Semua lapisan dekoratif (aria-hidden); cakram menerima ikon panah.
 * Ukuran: cakram 44px (target sentuh minimum), blok 56px, fillet 20px.
 */
export function NotchCorner({ children }: { children: ReactNode }) {
  return (
    <>
      <span
        aria-hidden="true"
        className="pointer-events-none absolute right-0 bottom-0 size-14 rounded-tl-[34px] rounded-br-3xl bg-background"
      />
      <span
        aria-hidden="true"
        className="pointer-events-none absolute right-0 bottom-14 size-5 bg-[radial-gradient(circle_at_top_left,transparent_19.5px,var(--background)_20px)]"
      />
      <span
        aria-hidden="true"
        className="pointer-events-none absolute right-14 bottom-0 size-5 bg-[radial-gradient(circle_at_top_left,transparent_19.5px,var(--background)_20px)]"
      />
      <span
        aria-hidden="true"
        className="absolute right-0 bottom-0 grid size-11 place-items-center rounded-full bg-soft-lavender text-primary transition-[background-color,color,scale] duration-300 group-hover:scale-105 group-hover:bg-primary group-hover:text-on-primary"
      >
        {children}
      </span>
    </>
  );
}
