/**
 * Gerak bersama (paket `motion`) untuk halaman Publik (Blade, lewat `public.ts`) dan area pengguna (React, lewat `AppShell`).
 * Penanda di HTML: data-load, data-reveal, data-stagger + data-item, data-float, data-lift
 * (+ data-tilt pada gambar di dalamnya), data-press. Bila pengguna meminta pengurangan gerak,
 * gerak tidak dijalankan dan semua elemen tampil normal. Elemen yang sudah diproses tidak
 * diproses ulang, jadi `initMotion()` aman dipanggil lagi setelah navigasi.
 */
import { animate, hover, inView, press } from 'motion';

type Kind = 'up' | 'left' | 'right' | 'pop';

const EASE = [0.2, 0.7, 0.2, 1] as const;

const done = new WeakSet<Element>();

function fresh(el: Element): boolean {
  if (done.has(el)) return false;
  done.add(el);
  return true;
}

function keyframes(kind: string | undefined): Record<string, number[]> {
  switch (kind as Kind | undefined) {
    case 'left':
      return { opacity: [0, 1], x: [-28, 0] };
    case 'right':
      return { opacity: [0, 1], x: [28, 0] };
    case 'pop':
      return { opacity: [0, 1], scale: [0.94, 1] };
    default:
      return { opacity: [0, 1], y: [24, 0] };
  }
}

/** Elemen yang sudah terlihat saat halaman dibuka tidak disembunyikan lagi agar tidak berkedip. */
function isBelowFold(el: Element): boolean {
  return el.getBoundingClientRect().top >= window.innerHeight;
}

function initLoad(): void {
  Array.from(document.querySelectorAll<HTMLElement>('[data-load]'))
    .filter(fresh)
    .forEach((el, index) => {
      animate(el, keyframes(el.dataset.load || undefined), {
        duration: 0.8,
        delay: 0.08 + index * 0.1,
        ease: EASE,
      });
    });
}

function initReveal(): void {
  Array.from(document.querySelectorAll<HTMLElement>('[data-reveal]'))
    .filter(fresh)
    .forEach((el) => {
      if (!isBelowFold(el)) return;
      el.style.opacity = '0';
      inView(
        el,
        () => {
          animate(el, keyframes(el.dataset.reveal || undefined), { duration: 0.7, ease: EASE });
        },
        { amount: 0.2 },
      );
    });

  Array.from(document.querySelectorAll<HTMLElement>('[data-stagger]'))
    .filter(fresh)
    .forEach((group) => {
      const items = Array.from(group.querySelectorAll<HTMLElement>('[data-item]'));
      if (items.length === 0 || !isBelowFold(group)) return;
      items.forEach((item) => {
        item.style.opacity = '0';
      });
      inView(
        group,
        () => {
          items.forEach((item, index) => {
            animate(item, keyframes(group.dataset.stagger || undefined), {
              duration: 0.65,
              delay: index * 0.12,
              ease: EASE,
            });
          });
        },
        { amount: 0.2 },
      );
    });
}

function initFloat(): void {
  Array.from(document.querySelectorAll<HTMLElement>('[data-float]'))
    .filter(fresh)
    .forEach((el, index) => {
      animate(el, { y: [0, -8, 0] }, { duration: 5, delay: index * 0.6, repeat: Infinity, ease: 'easeInOut' });
    });
}

function initHover(): void {
  Array.from(document.querySelectorAll<HTMLElement>('[data-lift]'))
    .filter(fresh)
    .forEach((card) => {
      const tilt = card.querySelector<HTMLElement>('[data-tilt]');
      const angle = Number(tilt?.dataset.tilt ?? 0);
      hover(card, () => {
        animate(card, { y: -6 }, { duration: 0.25, ease: EASE });
        if (tilt) animate(tilt, { scale: 1.1, rotate: angle }, { duration: 0.3, ease: EASE });
        return () => {
          animate(card, { y: 0 }, { duration: 0.3, ease: EASE });
          if (tilt) animate(tilt, { scale: 1, rotate: 0 }, { duration: 0.3, ease: EASE });
        };
      });
    });

  Array.from(document.querySelectorAll<HTMLElement>('[data-press]'))
    .filter(fresh)
    .forEach((el) => {
      press(el, () => {
        animate(el, { scale: 0.97 }, { duration: 0.1 });
        return () => {
          animate(el, { scale: 1 }, { duration: 0.2 });
        };
      });
    });
}

export function initMotion(): void {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  initLoad();
  initReveal();
  initFloat();
  initHover();
}
