import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

/**
 * Pesan di kartu kaca panel ilustrasi. Isinya diambil dari bank teks Beranda (Design bagian 3);
 * tidak ada testimoni atau klaim yang belum terkonfirmasi (RULE-049).
 */
const SLIDES = [
  {
    title: 'Pelan-pelan juga tidak apa-apa.',
    text: 'Mulai dari satu langkah kecil: catat mood, kerjakan asesmen, atau bercerita kepada RelaxMate.',
  },
  {
    title: 'Privasi dijaga.',
    text: 'Isi catatan dan percakapanmu dienkripsi, dan tinjauan kualitas dilakukan tanpa identitasmu.',
  },
  {
    title: 'Bukan diagnosis.',
    text: 'Hasil asesmen hanya gambaran awal, bukan penilaian medis.',
  },
] as const;

/** Kartu kaca dengan tiga pesan dan tombol panah. Tidak berganti sendiri agar tidak mengganggu. */
export default function AuthShowcase() {
  const [index, setIndex] = useState(0);
  const slide = SLIDES[index];
  const move = (step: number) => setIndex((current) => (current + step + SLIDES.length) % SLIDES.length);

  const arrow =
    'grid size-8 place-items-center rounded-full border border-card/70 bg-card/50 text-text transition-all duration-200 hover:-translate-y-px hover:bg-card hover:text-nav-hover hover:shadow-lift active:scale-95';

  return (
    <section
      aria-roledescription="carousel"
      aria-label="Tentang RelaxBoss"
      className="rounded-2xl border border-card/70 bg-card/55 p-4 shadow-lift backdrop-blur-md"
    >
      <div aria-live="polite" className="min-h-[4.5rem]">
        <p key={index} className="text-[0.9375rem] leading-snug font-semibold text-text motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-500">
          {slide.title}
        </p>
        <p key={`t-${index}`} className="mt-1 text-[0.8125rem] leading-relaxed text-text-secondary motion-safe:animate-in motion-safe:fade-in motion-safe:duration-700">
          {slide.text}
        </p>
      </div>

      <div className="mt-3 flex items-center justify-between">
        <div className="flex items-center gap-1.5" aria-hidden="true">
          {SLIDES.map((item, position) => (
            <span
              key={item.title}
              className={`h-1.5 rounded-full transition-all duration-300 ${position === index ? 'w-5 bg-primary' : 'w-1.5 bg-primary/25'}`}
            />
          ))}
        </div>
        <div className="flex items-center gap-1.5">
          <button type="button" className={arrow} aria-label="Pesan sebelumnya" onClick={() => move(-1)}>
            <ChevronLeft className="size-4" aria-hidden="true" />
          </button>
          <button type="button" className={arrow} aria-label="Pesan berikutnya" onClick={() => move(1)}>
            <ChevronRight className="size-4" aria-hidden="true" />
          </button>
        </div>
      </div>
    </section>
  );
}
