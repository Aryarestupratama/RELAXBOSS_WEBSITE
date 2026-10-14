/**
 * Mesin animasi maskot RelaxMate (sprite sheet pixel-art 8 bingkai per state).
 * Diport dari prototipe `mascot.js` + skrip penggerak di `app.html`; logika urutan bingkai tidak diubah.
 * Dipakai oleh `Components/shared/Mascot.tsx`. Bila `prefers-reduced-motion: reduce`, hanya pose diam yang tampil.
 */

export type MascotName = 'idle' | 'listening' | 'thinking' | 'speaking';

type Sequence = { seq: number[]; ms: number[] };
type Range = [number, number];

type MascotEvent = Sequence & { name: string; weight: number };

type MascotState = {
  img: string;
  /** Bingkai pose diam (dipakai saat gerak dikurangi). */
  rest?: number;
  /** Mode acak (idle): diam sebentar, lalu satu kejadian berbobot (kedip, lirik). */
  events?: MascotEvent[];
  restMs?: Range;
  /** Mode berulang (listening, thinking). */
  intro?: Sequence;
  seq?: number[];
  ms?: number[];
  extras?: Sequence & { every: Range };
  exit?: Sequence;
  /** Mode acak (speaking): bentuk mulut terbuka berganti acak, sesekali menutup. */
  random?: { open: number[]; closed: number[]; msRange: Range; closedChance: number };
};

export const MASCOT_FRAME_WIDTH = 320;
export const MASCOT_FRAME_HEIGHT = 460;
export const MASCOT_FRAMES = 8;

export const MASCOT_STATES: Record<MascotName, MascotState> = {
  idle: {
    img: '/images/relaxmate/idle.webp',
    rest: 1,
    restMs: [2200, 4200],
    events: [
      { name: 'blink', weight: 45, seq: [7, 8, 7], ms: [90, 130, 90] },
      { name: 'lookLeft', weight: 20, seq: [2, 3, 3, 2], ms: [180, 650, 350, 180] },
      { name: 'lookRight', weight: 20, seq: [5, 6, 6, 5], ms: [180, 650, 350, 180] },
      { name: 'doubleBlink', weight: 15, seq: [7, 8, 7, 1, 7, 8, 7], ms: [90, 120, 90, 160, 90, 120, 90] },
    ],
  },
  listening: {
    img: '/images/relaxmate/listening.webp',
    intro: { seq: [1, 2, 3], ms: [250, 200, 300] },
    seq: [4, 5, 6, 5],
    ms: [600, 500, 600, 500],
    extras: { seq: [7], ms: [900], every: [2, 3] },
    exit: { seq: [2, 1], ms: [180, 220] },
  },
  speaking: {
    img: '/images/relaxmate/speaking.webp',
    rest: 1,
    random: { open: [2, 3, 4, 6, 7], closed: [1, 5, 8], msRange: [110, 180], closedChance: 0.35 },
  },
  thinking: {
    img: '/images/relaxmate/thinking.webp',
    intro: { seq: [1, 2], ms: [250, 250] },
    seq: [3, 4, 5, 6, 7, 8],
    ms: [450, 350, 350, 350, 500, 450],
  },
};

type Frame = { f: number; ms: number };

type Run = {
  idx: number;
  introIdx: number;
  queue: Frame[];
  cycles: number;
  atRest: boolean;
  lastOpen: boolean;
  last: number;
};

export type MascotPlayer = {
  setState: (name: MascotName) => void;
  destroy: () => void;
};

const rand = ([min, max]: Range): number => Math.floor(Math.random() * (max - min + 1)) + min;

const toQueue = (seq: number[], ms: number[]): Frame[] => seq.map((f, i) => ({ f, ms: ms[i] }));

function weighted(list: MascotEvent[]): MascotEvent {
  let r = Math.random() * list.reduce((sum, event) => sum + event.weight, 0);
  for (const event of list) {
    r -= event.weight;
    if (r < 0) {
      return event;
    }
  }
  return list[0];
}

let preloaded = false;

function preload(): void {
  if (preloaded) {
    return;
  }
  preloaded = true;
  Object.values(MASCOT_STATES).forEach((state) => {
    const image = new Image();
    image.src = state.img;
    image.decode().catch(() => undefined);
  });
}

/** Menjalankan animasi pada satu elemen. `--f` (nomor bingkai, mulai 0) dibaca CSS elemen untuk posisi latar. */
export function createMascotPlayer(el: HTMLElement): MascotPlayer {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let state: MascotName | null = null;
  let target: MascotName | null = null;
  let timer: ReturnType<typeof setTimeout> | undefined;
  let run: Run = { idx: 0, introIdx: 0, queue: [], cycles: 0, atRest: false, lastOpen: false, last: 0 };
  let generation = 0;

  preload();

  const show = (frame: number): void => {
    el.style.setProperty('--f', String(frame - 1));
  };

  const tick = (): void => {
    if (state === null) {
      return;
    }
    const config = MASCOT_STATES[state];
    let delay: number;

    const play = (): number => {
      const next = run.queue.shift();
      if (!next) {
        return 0;
      }
      show(next.f);
      return next.ms;
    };

    if (config.random) {
      const r = config.random;
      let frame: number;
      if (run.lastOpen && Math.random() < r.closedChance) {
        frame = r.closed[Math.floor(Math.random() * r.closed.length)];
        run.lastOpen = false;
      } else {
        do {
          frame = r.open[Math.floor(Math.random() * r.open.length)];
        } while (frame === run.last && r.open.length > 1);
        run.lastOpen = true;
      }
      run.last = frame;
      show(frame);
      delay = rand(r.msRange);
    } else if (config.events && config.restMs && config.rest !== undefined) {
      if (run.queue.length > 0) {
        delay = play();
      } else if (!run.atRest) {
        show(config.rest);
        run.atRest = true;
        delay = rand(config.restMs);
      } else {
        const event = weighted(config.events);
        run.atRest = false;
        run.queue = toQueue(event.seq, event.ms);
        delay = play();
      }
    } else if (run.queue.length > 0) {
      delay = play();
    } else if (config.intro && run.introIdx < config.intro.seq.length) {
      show(config.intro.seq[run.introIdx]);
      delay = config.intro.ms[run.introIdx];
      run.introIdx += 1;
    } else if (config.seq && config.ms) {
      show(config.seq[run.idx]);
      delay = config.ms[run.idx];
      run.idx = (run.idx + 1) % config.seq.length;
      if (run.idx === 0 && config.extras) {
        run.cycles -= 1;
        if (run.cycles <= 0) {
          run.queue = toQueue(config.extras.seq, config.extras.ms);
          run.cycles = rand(config.extras.every);
        }
      }
    } else {
      return;
    }

    timer = setTimeout(tick, delay);
  };

  const enter = (name: MascotName): void => {
    state = name;
    const config = MASCOT_STATES[name];
    run = {
      idx: 0,
      introIdx: 0,
      queue: [],
      cycles: config.extras ? rand(config.extras.every) : 0,
      atRest: false,
      lastOpen: false,
      last: 0,
    };
    // Semua sheet sudah terpasang sebagai lapisan; hanya mengganti mana yang tampil (tanpa memuat ulang gambar, jadi tanpa kedip).
    el.querySelectorAll<HTMLElement>('[data-sheet]').forEach((layer) => {
      layer.style.visibility = layer.dataset.sheet === name ? 'visible' : 'hidden';
    });
    if (reduce) {
      show(config.rest ?? 1);
      return;
    }
    tick();
  };

  const setState = (name: MascotName): void => {
    if (name === target) {
      return;
    }
    target = name;
    generation += 1;
    const mine = generation;
    clearTimeout(timer);

    const current = state ? MASCOT_STATES[state] : null;
    if (!reduce && current?.exit && state !== name) {
      const exit = current.exit;
      let i = 0;
      const step = (): void => {
        if (mine !== generation) {
          return;
        }
        if (i < exit.seq.length) {
          show(exit.seq[i]);
          const wait = exit.ms[i];
          i += 1;
          timer = setTimeout(step, wait);
        } else {
          enter(name);
        }
      };
      step();
      return;
    }
    enter(name);
  };

  return {
    setState,
    destroy: () => {
      generation += 1;
      clearTimeout(timer);
    },
  };
}
