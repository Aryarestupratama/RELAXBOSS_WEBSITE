import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

/**
 * Efek mengetik untuk Rekomendasi AI (TASK-056, SCR-014). Waktu tidak sama rata: koma, titik, dan baris baru memberi
 * jeda kecil seperti orang mengetik. Seluruh teks selesai dalam waktu yang dibatasi (tidak terlalu cepat untuk teks pendek,
 * tidak terlalu lama untuk teks panjang). Karakter dihitung per titik kode, bukan per unit UTF-16, supaya emoji tidak terbelah.
 */

/** Biaya waktu relatif satu karakter: biasa 1; jeda setelah tanda baca dan baris baru lebih lama. */
const COST_BASE = 1;
const COST_COMMA = 3;
const COST_SENTENCE = 7;
const COST_NEWLINE = 9;

/** Kecepatan acuan (karakter per detik) dan batas lama total animasi, dalam milidetik. */
const TARGET_CPS = 60;
const MIN_DURATION_MS = 2500;
const MAX_DURATION_MS = 9000;

function costOf(char: string): number {
  if (char === '\n') {
    return COST_NEWLINE;
  }
  if (char === '.' || char === '!' || char === '?') {
    return COST_SENTENCE;
  }
  if (char === ',' || char === ';' || char === ':') {
    return COST_COMMA;
  }
  return COST_BASE;
}

/** Biaya kumulatif setelah tiap karakter (panjang sama dengan jumlah titik kode). */
export function typingSchedule(chars: string[]): number[] {
  const schedule: number[] = [];
  let total = 0;
  for (const char of chars) {
    total += costOf(char);
    schedule.push(total);
  }
  return schedule;
}

/** Jumlah karakter yang sudah tampil setelah [cost] satuan waktu berlalu (pencarian biner). */
export function charsAt(schedule: number[], cost: number): number {
  let low = 0;
  let high = schedule.length;
  while (low < high) {
    const mid = (low + high) >> 1;
    if (schedule[mid] <= cost) {
      low = mid + 1;
    } else {
      high = mid;
    }
  }
  return low;
}

/** Lama total animasi untuk [count] karakter. */
export function typingDuration(count: number): number {
  return Math.min(Math.max((count / TARGET_CPS) * 1000, MIN_DURATION_MS), MAX_DURATION_MS);
}

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export type Typewriter = {
  /** Jumlah titik kode yang tampil untuk tiap teks, berurutan (teks kedua baru mulai setelah teks pertama selesai). */
  shown: number[];
  done: boolean;
  /** Menampilkan semuanya sekarang. */
  skip: () => void;
};

/**
 * Menampilkan [texts] berurutan seperti diketik. Bila [enabled] false atau pengguna meminta pengurangan gerak, semuanya langsung
 * tampil. Isi `texts` harus tetap sama selama animasi berjalan (hasil AI tidak berubah setelah tiba); identitas array boleh berganti.
 */
export function useTypewriter(texts: string[], enabled: boolean): Typewriter {
  // Kunci berdasarkan ISI, bukan identitas array. Pemanggil yang membuat array baru di tiap render tidak boleh mengulang
  // animasi dari awal: setiap `setCount` merender ulang, dan array baru akan membuat jadwal baru yang mereset efek
  // sehingga teks tertahan di satu huruf dan berkedip (bug TASK-057).
  const key = JSON.stringify(texts);
  const chars = useMemo(() => (JSON.parse(key) as string[]).map((text) => Array.from(text)), [key]);
  const lengths = useMemo(() => chars.map((list) => list.length), [chars]);
  const total = useMemo(() => lengths.reduce((sum, length) => sum + length, 0), [lengths]);
  const schedule = useMemo(() => typingSchedule(chars.flat()), [chars]);

  const instant = !enabled || total === 0;
  const [count, setCount] = useState(() => (instant || prefersReducedMotion() ? total : 0));
  const frame = useRef<number | undefined>(undefined);

  useEffect(() => {
    if (instant || prefersReducedMotion()) {
      setCount(total);
      return;
    }
    const totalCost = schedule[schedule.length - 1] ?? 0;
    const costPerMs = totalCost / typingDuration(total);
    const start = performance.now();

    const tick = (now: number) => {
      const next = charsAt(schedule, (now - start) * costPerMs);
      setCount((current) => (next === current ? current : next));
      if (next < total) {
        frame.current = requestAnimationFrame(tick);
      }
    };
    frame.current = requestAnimationFrame(tick);

    return () => {
      if (frame.current !== undefined) {
        cancelAnimationFrame(frame.current);
      }
    };
  }, [instant, total, schedule]);

  const skip = useCallback(() => {
    if (frame.current !== undefined) {
      cancelAnimationFrame(frame.current);
    }
    setCount(total);
  }, [total]);

  const shown: number[] = [];
  let remaining = count;
  for (const length of lengths) {
    shown.push(Math.min(remaining, length));
    remaining = Math.max(0, remaining - length);
  }

  return { shown, done: count >= total, skip };
}
