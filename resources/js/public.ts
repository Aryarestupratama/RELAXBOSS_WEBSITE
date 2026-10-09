/**
 * Gerak halaman Publik (Blade). Satu entri kecil tanpa React; hanya memakai paket `motion`.
 * Penanda di HTML: data-load, data-reveal, data-stagger + data-item, data-float, data-lift
 * (+ data-tilt pada gambar di dalamnya), data-press. Bila pengguna meminta pengurangan gerak,
 * gerak tidak dijalankan dan semua elemen tampil normal. Penanda daftar isi (data-toc-link) bukan
 * gerak, jadi tetap berjalan.
 */
import { animate, hover, inView, press } from 'motion';

type Kind = 'up' | 'left' | 'right' | 'pop';

const EASE = [0.2, 0.7, 0.2, 1] as const;

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
    document.querySelectorAll<HTMLElement>('[data-load]').forEach((el, index) => {
        animate(el, keyframes(el.dataset.load || undefined), {
            duration: 0.8,
            delay: 0.08 + index * 0.1,
            ease: EASE,
        });
    });
}

function initReveal(): void {
    document.querySelectorAll<HTMLElement>('[data-reveal]').forEach((el) => {
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

    document.querySelectorAll<HTMLElement>('[data-stagger]').forEach((group) => {
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
    document.querySelectorAll<HTMLElement>('[data-float]').forEach((el, index) => {
        animate(el, { y: [0, -8, 0] }, { duration: 5, delay: index * 0.6, repeat: Infinity, ease: 'easeInOut' });
    });
}

function initHover(): void {
    document.querySelectorAll<HTMLElement>('[data-lift]').forEach((card) => {
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

    document.querySelectorAll<HTMLElement>('[data-press]').forEach((el) => {
        press(el, () => {
            animate(el, { scale: 0.97 }, { duration: 0.1 });
            return () => {
                animate(el, { scale: 1 }, { duration: 0.2 });
            };
        });
    });
}

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

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    initLoad();
    initReveal();
    initFloat();
    initHover();
}
