import { useEffect, useLayoutEffect, useRef, type RefObject } from 'react';
import { animate } from 'motion';

/**
 * Gerak untuk halaman area pengguna yang ditulis dengan React (paket `motion` yang sama dengan
 * halaman Publik). Memakai penanda `data-m-*` agar tidak bertabrakan dengan penanda `data-*` lain.
 * Pengguna dengan `prefers-reduced-motion: reduce` tidak mendapat gerak apa pun.
 */

const EASE = [0.2, 0.7, 0.2, 1] as const;

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Elemen bertanda `data-m-enter` di dalam wadah masuk bertahap (naik halus dan memudar) saat
 * halaman dibuka. Hanya berjalan sekali, jadi menyimpan catatan tidak mengulang gerak.
 */
export function useEnter<T extends HTMLElement>(): RefObject<T | null> {
  const ref = useRef<T | null>(null);

  useLayoutEffect(() => {
    const root = ref.current;
    if (!root || prefersReducedMotion()) {
      return;
    }
    const items = Array.from(root.querySelectorAll<HTMLElement>('[data-m-enter]'));
    items.forEach((item) => {
      item.style.opacity = '0';
    });
    const controls = items.map((item, index) =>
      animate(item, { opacity: [0, 1], y: [18, 0] }, { duration: 0.55, delay: 0.05 + index * 0.08, ease: EASE }),
    );
    return () => {
      controls.forEach((control) => control.cancel());
      items.forEach((item) => {
        item.style.opacity = '';
      });
    };
  }, []);

  return ref;
}

/** Hiasan yang melayang pelan naik turun. */
export function useFloat<T extends HTMLElement>(delay = 0): RefObject<T | null> {
  const ref = useRef<T | null>(null);

  useEffect(() => {
    const element = ref.current;
    if (!element || prefersReducedMotion()) {
      return;
    }
    const control = animate(element, { y: [0, -6, 0] }, { duration: 5, delay, repeat: Infinity, ease: 'easeInOut' });
    return () => control.cancel();
  }, [delay]);

  return ref;
}
