import { useEffect, useRef } from 'react';
import {
  createMascotPlayer,
  MASCOT_BASE,
  MASCOT_CLIPS,
  MASCOT_FRAMES,
  MASCOT_FRAME_HEIGHT,
  MASCOT_FRAME_WIDTH,
  MASCOT_SEATED,
  MASCOT_STATES,
  type MascotClipName,
  type MascotName,
  type MascotPlayer,
} from '@/lib/mascot';

type MascotProps = {
  state: MascotName;
  /** Posisi dan ukuran. Skala diatur lewat variabel CSS, mis. `[--s:0.6]` (1 = 320 x 460 px). */
  className?: string;
  /** Duduk di kursi: idle, listening, thinking, speaking dipetakan ke versi duduknya. */
  seated?: boolean;
  /** Dengan `seated`: saat dipasang, robot masuk dulu (jalan, ambil kursi, bawa, duduk). */
  entrance?: boolean;
  /** Dipanggil setelah urutan masuk selesai (atau dilewati karena gerak dikurangi). */
  onSeated?: () => void;
  /** Dengan `state="walk"`: robot berjalan keluar ke kanan. Induk perlu `overflow-hidden`. */
  walkOutMs?: number;
};

const CLIP_NAMES = Object.keys(MASCOT_CLIPS) as MascotClipName[];

/**
 * Maskot RelaxMate beranimasi (sprite pixel-art). Hiasan: `aria-hidden`, tidak membawa informasi.
 * `state`: idle (kedip dan lirik acak), listening (menyimak), thinking (berpikir), speaking (berbicara),
 * walk (berjalan keluar). Dengan `seated`, empat keadaan pertama memakai sheet duduk di kursi.
 * Setiap state punya lapisan sheet sendiri yang selalu terpasang; pindah state hanya menukar lapisan yang terlihat,
 * sehingga tidak ada kedipan akibat gambar dimuat ulang. `image-rendering: pixelated` menjaga garis piksel tetap tegas.
 */
export default function Mascot({ state, className = '', seated = false, entrance = false, onSeated, walkOutMs }: MascotProps) {
  const ref = useRef<HTMLDivElement>(null);
  const player = useRef<MascotPlayer | null>(null);
  const onSeatedRef = useRef(onSeated);
  onSeatedRef.current = onSeated;

  const effective: MascotName =
    seated && (MASCOT_BASE as string[]).includes(state) ? MASCOT_SEATED[state as keyof typeof MASCOT_SEATED] : state;
  const sheets: Array<{ name: string; img: string }> = [
    ...(seated ? Object.values(MASCOT_SEATED) : MASCOT_BASE).map((name) => ({ name, img: MASCOT_STATES[name].img })),
    ...(walkOutMs ? [{ name: 'walk', img: MASCOT_STATES.walk.img }] : []),
    ...(seated && entrance ? CLIP_NAMES.map((name) => ({ name, img: MASCOT_CLIPS[name].img })) : []),
  ];

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

  // Urutan masuk: jalan dari luar sisi kiri, ambil kursi, bawa ke tempat duduk, duduk.
  useEffect(() => {
    const el = ref.current;
    const p = player.current;
    if (!el || !p || !seated || !entrance) {
      return;
    }
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) {
      onSeatedRef.current?.();
      return;
    }
    const scale = parseFloat(getComputedStyle(el).getPropertyValue('--s')) || 1;
    const cell = MASCOT_FRAME_WIDTH * scale;
    const room = (el.closest('[data-mascot-stage]') ?? el.parentElement)?.clientWidth ?? cell;
    const half = room / 2;
    const chairX = -Math.min(cell * 0.45, half * 0.6);

    let cancelled = false;
    p.move(-(half + cell / 2 + 8), 0);
    void el.offsetWidth;

    const run = async (): Promise<void> => {
      const steps: Array<() => Promise<boolean>> = [
        () => p.playClip('walkIn', { loop: 3, moveTo: chairX }),
        () => p.playClip('take'),
        () => p.playClip('carry', { loop: 2, moveTo: 0 }),
        () => p.playClip('sitDown'),
      ];
      for (const step of steps) {
        if (cancelled || !(await step())) {
          return;
        }
      }
      p.move(0, 0);
      p.resume();
      onSeatedRef.current?.();
    };
    void run();

    return () => {
      cancelled = true;
    };
    // Urutan masuk dimulai sekali, saat dipasang.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    player.current?.setState(effective);
  }, [effective]);

  // Berjalan keluar: geser ke kanan selama animasi jalan; kembali ke tempat semula bila batal.
  useEffect(() => {
    const el = ref.current;
    const p = player.current;
    if (!el || !p || !walkOutMs) {
      return;
    }
    if (state === 'walk' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      const scale = parseFloat(getComputedStyle(el).getPropertyValue('--s')) || 1;
      const cell = MASCOT_FRAME_WIDTH * scale;
      const room = (el.parentElement?.clientWidth ?? cell) / 2;
      p.move(room + cell / 2 + 8, walkOutMs);
    } else if (state !== 'walk') {
      p.move(0, 0);
    }
  }, [state, walkOutMs]);

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
      {sheets.map(({ name, img }) => (
        <div
          key={name}
          data-sheet={name}
          className="bg-no-repeat"
          style={{
            gridArea: '1 / 1',
            visibility: 'hidden',
            backgroundImage: `url("${img}")`,
            backgroundSize: `calc(${MASCOT_FRAME_WIDTH * MASCOT_FRAMES}px * var(--s, 1)) calc(${MASCOT_FRAME_HEIGHT}px * var(--s, 1))`,
            backgroundPosition: `calc(var(--f, 0) * -${MASCOT_FRAME_WIDTH}px * var(--s, 1)) 0`,
            imageRendering: 'pixelated',
            transform: 'translateX(var(--x, 0px))',
            transition: 'transform var(--xt, 0ms) linear',
          }}
        />
      ))}
    </div>
  );
}
