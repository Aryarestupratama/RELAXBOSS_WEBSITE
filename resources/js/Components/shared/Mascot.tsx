import { useEffect, useRef } from 'react';
import {
  createMascotPlayer,
  MASCOT_FRAMES,
  MASCOT_FRAME_HEIGHT,
  MASCOT_FRAME_WIDTH,
  MASCOT_STATES,
  type MascotName,
  type MascotPlayer,
} from '@/lib/mascot';

type MascotProps = {
  state: MascotName;
  /** Posisi dan ukuran. Skala diatur lewat variabel CSS, mis. `[--s:0.6]` (1 = 320 x 460 px). */
  className?: string;
};

const NAMES = Object.keys(MASCOT_STATES) as MascotName[];

/**
 * Maskot RelaxMate beranimasi (sprite pixel-art). Hiasan: `aria-hidden`, tidak membawa informasi.
 * `state`: idle (kedip dan lirik acak), listening (menyimak), thinking (berpikir), speaking (berbicara).
 * Setiap state punya lapisan sheet sendiri yang selalu terpasang; pindah state hanya menukar lapisan yang terlihat,
 * sehingga tidak ada kedipan akibat gambar dimuat ulang. `image-rendering: pixelated` menjaga garis piksel tetap tegas.
 */
export default function Mascot({ state, className = '' }: MascotProps) {
  const ref = useRef<HTMLDivElement>(null);
  const player = useRef<MascotPlayer | null>(null);

  // Efek pembuat dideklarasikan lebih dulu sehingga selalu berjalan sebelum efek `state` (juga pada StrictMode).
  useEffect(() => {
    const el = ref.current;
    if (!el) {
      return;
    }
    player.current = createMascotPlayer(el);
    return () => {
      player.current?.destroy();
      player.current = null;
    };
  }, []);

  useEffect(() => {
    player.current?.setState(state);
  }, [state]);

  return (
    <div
      ref={ref}
      aria-hidden="true"
      className={`grid shrink-0 ${className}`}
      style={{
        width: `calc(${MASCOT_FRAME_WIDTH}px * var(--s, 1))`,
        height: `calc(${MASCOT_FRAME_HEIGHT}px * var(--s, 1))`,
      }}
    >
      {NAMES.map((name) => (
        <div
          key={name}
          data-sheet={name}
          className="bg-no-repeat"
          style={{
            gridArea: '1 / 1',
            visibility: 'hidden',
            backgroundImage: `url("${MASCOT_STATES[name].img}")`,
            backgroundSize: `calc(${MASCOT_FRAME_WIDTH * MASCOT_FRAMES}px * var(--s, 1)) calc(${MASCOT_FRAME_HEIGHT}px * var(--s, 1))`,
            backgroundPosition: `calc(var(--f, 0) * -${MASCOT_FRAME_WIDTH}px * var(--s, 1)) 0`,
            imageRendering: 'pixelated',
          }}
        />
      ))}
    </div>
  );
}
