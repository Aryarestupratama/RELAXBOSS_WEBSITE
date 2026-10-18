import { useEffect, useState } from 'react';

/**
 * Menandai bagian halaman yang sedang dibaca (untuk menu bagian). Bukan gerak, jadi tetap jalan saat gerak dikurangi.
 * Di dasar halaman, bagian terakhir dianggap aktif.
 */
export function useActiveSection(ids: string[]): string {
  const [active, setActive] = useState(ids[0]);
  const key = ids.join('|');

  useEffect(() => {
    let frame = 0;

    const update = (): void => {
      frame = 0;
      const atBottom = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 4;
      let current = ids[0];
      for (const id of ids) {
        const element = document.getElementById(id);
        if (element && element.getBoundingClientRect().top <= 140) {
          current = id;
        }
      }
      setActive(atBottom ? ids[ids.length - 1] : current);
    };

    const onScroll = (): void => {
      if (frame === 0) {
        frame = window.requestAnimationFrame(update);
      }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    update();

    return () => {
      window.removeEventListener('scroll', onScroll);
      if (frame !== 0) {
        window.cancelAnimationFrame(frame);
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]);

  return active;
}
