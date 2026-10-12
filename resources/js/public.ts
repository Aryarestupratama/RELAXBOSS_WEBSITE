/**
 * Entri halaman Publik (Blade). Satu entri kecil tanpa React. Gerak ada di `lib/motion.ts`
 * (dipakai juga area pengguna); penanda daftar isi (data-toc-link) bukan gerak, jadi tetap berjalan
 * walau pengguna meminta pengurangan gerak.
 */
import { initMotion } from './lib/motion';

/** Daftar isi halaman hukum (Privasi, Ketentuan): menandai bagian yang sedang dibaca lewat aria-current. */
function initToc(): void {
    const links = Array.from(document.querySelectorAll<HTMLAnchorElement>('[data-toc-link]'));
    const sections = links
        .map((link) => document.getElementById(link.hash.slice(1)))
        .filter((section): section is HTMLElement => section !== null);
    if (sections.length === 0) return;

    let frame = 0;
    const update = (): void => {
        frame = 0;
        const atBottom = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 4;
        let current = 0;
        sections.forEach((section, index) => {
            if (section.getBoundingClientRect().top <= 140) current = index;
        });
        if (atBottom) current = sections.length - 1;
        links.forEach((link, index) => {
            if (index === current) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
        });
    };

    window.addEventListener(
        'scroll',
        () => {
            if (frame === 0) frame = window.requestAnimationFrame(update);
        },
        { passive: true },
    );
    update();
}

initToc();

initMotion();
