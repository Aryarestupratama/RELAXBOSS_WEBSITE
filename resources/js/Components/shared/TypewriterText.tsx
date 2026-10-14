import { useEffect, useMemo, useRef, useState } from 'react';

type TypewriterTextProps = {
  text: string;
  /** Dipanggil tiap teks bertambah (untuk menggulir kotak pesan). */
  onTick?: () => void;
  onDone?: () => void;
};

const MS_PER_CHAR = 28;
const MAX_TOTAL_MS = 6000;

/**
 * Menampilkan teks bertahap seperti diketik. Pembaca layar mendapat teks penuh (sr-only) sekali,
 * bukan potongan yang terus berubah. Bila pengguna meminta gerak dikurangi, teks langsung tampil.
 */
export default function TypewriterText({ text, onTick, onDone }: TypewriterTextProps) {
  const chars = useMemo(() => Array.from(text), [text]);
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const [count, setCount] = useState(reduce ? chars.length : 0);
  const tick = useRef(onTick);
  const done = useRef(onDone);
  tick.current = onTick;
  done.current = onDone;

  useEffect(() => {
    if (reduce || chars.length === 0) {
      setCount(chars.length);
      done.current?.();
      return;
    }
    const perChar = Math.min(MS_PER_CHAR, MAX_TOTAL_MS / chars.length);
    const start = performance.now();
    let frame = 0;
    const step = (now: number): void => {
      const next = Math.min(chars.length, Math.floor((now - start) / perChar));
      setCount(next);
      tick.current?.();
      if (next >= chars.length) {
        done.current?.();
        return;
      }
      frame = requestAnimationFrame(step);
    };
    frame = requestAnimationFrame(step);
    return () => cancelAnimationFrame(frame);
  }, [chars, reduce]);

  return (
    <>
      <span className="sr-only">{text}</span>
      <span aria-hidden="true">{chars.slice(0, count).join('')}</span>
    </>
  );
}
