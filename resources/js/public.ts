/**
 * Gerak halaman Publik (Blade). Satu entri kecil tanpa React; hanya memakai paket `motion`.
 * Penanda di HTML: data-load, data-reveal, data-stagger + data-item, data-float, data-lift
 * (+ data-tilt pada gambar di dalamnya), data-press. Bila pengguna meminta pengurangan gerak,
 * berkas ini tidak melakukan apa pun dan semua elemen tampil normal.
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

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    initLoad();
    initReveal();
    initFloat();
    initHover();
}
