import { useEffect, useRef, type ReactNode } from 'react';
import { createPortal } from 'react-dom';
import { Minimize2 } from 'lucide-react';
import { Button } from '@/Components/ui/button';

type QuietRoomProps = {
  open: boolean;
  onClose: () => void;
  /** Baris kecil di kiri atas (tanggal, mood). */
  header?: ReactNode;
  /** Bilah bawah yang tenang (penghitung dan tombol simpan). */
  footer?: ReactNode;
  children: ReactNode;
};

/**
 * Lampu baca: hanya kepala lampu (tanpa tiang), besar, terlihat dari meja tempat orang menulis. Kap kubah menggantung di
 * pojok kanan atas dan sedikit terpotong tepi layar; mulutnya menghadap kertas di kiri bawah (sumbu cahaya 56 derajat dari
 * tegak lurus). Dua lapisan dengan geometri sama: `cone` di BAWAH kertas (cahaya di udara, tidak menutupi tulisan) dan
 * `head` di ATAS kertas. Kertas sendiri diterangi di titik jatuh kerucut lewat `.writing-viewport[data-quiet]`.
 * Hiasan murni (`aria-hidden`); semua warna dari token. Sumbu kap = sumbu +y pada grup yang diputar.
 */
const LAMP_TRANSFORM = 'translate(235 185) rotate(56)';

function LampCone() {
  return (
    <div aria-hidden="true" className="quiet-lamp quiet-lamp-cone">
      <svg viewBox="0 0 400 300" focusable="false">
        <defs>
          <filter id="quiet-soft" x="-60%" y="-30%" width="220%" height="180%">
            <feGaussianBlur stdDeviation="16" />
          </filter>
          <linearGradient id="quiet-cone" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="0" y2="820">
            <stop offset="0" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0.5 }} />
            <stop offset="0.5" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0.16 }} />
            <stop offset="1" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0 }} />
          </linearGradient>
          <radialGradient id="quiet-halo" cx="0.5" cy="0.5" r="0.5">
            <stop offset="0" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0.55 }} />
            <stop offset="1" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0 }} />
          </radialGradient>
        </defs>
        <g transform={LAMP_TRANSFORM}>
          <polygon points="-96,6 96,6 270,820 -270,820" fill="url(#quiet-cone)" filter="url(#quiet-soft)" />
          <ellipse cx="0" cy="14" rx="150" ry="70" fill="url(#quiet-halo)" />
        </g>
      </svg>
    </div>
  );
}

function LampHead() {
  return (
    <div aria-hidden="true" className="quiet-lamp quiet-lamp-head">
      <svg viewBox="0 0 400 300" focusable="false">
        <defs>
          <linearGradient id="quiet-shade" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" style={{ stopColor: 'var(--primary)' }} />
            <stop offset="0.45" style={{ stopColor: 'var(--nav-hover)' }} />
            <stop offset="1" style={{ stopColor: 'var(--primary)' }} />
          </linearGradient>
          <radialGradient id="quiet-mouth" cx="0.5" cy="0.45" r="0.6">
            <stop offset="0" style={{ stopColor: 'var(--on-primary)' }} />
            <stop offset="0.35" style={{ stopColor: 'var(--mood-yellow)' }} />
            <stop offset="1" style={{ stopColor: 'var(--mood-yellow)', stopOpacity: 0.55 }} />
          </radialGradient>
        </defs>
        <g transform={LAMP_TRANSFORM}>
          {/* Kenop kecil di puncak kap. */}
          <rect x="-15" y="-142" width="30" height="24" rx="7" style={{ fill: 'var(--nav-hover)' }} />
          <path
            d="M -100 0 C -100 -62 -64 -112 -32 -122 L 32 -122 C 64 -112 100 -62 100 0 Z"
            fill="url(#quiet-shade)"
          />
          <path
            d="M -97 -4 C -96 -58 -62 -106 -31 -118"
            fill="none"
            strokeWidth="2.5"
            strokeLinecap="round"
            style={{ stroke: 'var(--mood-yellow)', strokeOpacity: 0.4 }}
          />
          {/* Mulut kap yang menyala. */}
          <ellipse cx="0" cy="0" rx="100" ry="22" fill="url(#quiet-mouth)" />
          <ellipse cx="0" cy="0" rx="100" ry="22" fill="none" strokeWidth="3" style={{ stroke: 'var(--primary)' }} />
        </g>
      </svg>
    </div>
  );
}

export default function QuietRoom({ open, onClose, header, footer, children }: QuietRoomProps) {
  const opener = useRef<HTMLElement | null>(null);
  const dialog = useRef<HTMLDivElement>(null);
  // `onClose` boleh berganti identitas tiap render tanpa menjalankan ulang pengaturan di bawah (yang mengembalikan fokus).
  const closeRef = useRef(onClose);

  useEffect(() => {
    closeRef.current = onClose;
  }, [onClose]);

  useEffect(() => {
    if (!open) {
      return;
    }
    opener.current = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    // Semua yang ada di belakang (aplikasi Inertia) dimatikan; hanya ruang sunyi yang bisa difokus dan dibaca.
    const behind = Array.from(document.body.children).filter(
      (element) => element !== dialog.current && !(element instanceof HTMLScriptElement),
    );
    behind.forEach((element) => element.setAttribute('inert', ''));
    const overflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        closeRef.current();
      }
    };
    document.addEventListener('keydown', onKey);

    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = overflow;
      behind.forEach((element) => element.removeAttribute('inert'));
      opener.current?.focus();
    };
  }, [open]);

  if (!open) {
    return null;
  }

  return createPortal(
    <div
      ref={dialog}
      role="dialog"
      aria-modal="true"
      aria-label="Ruang sunyi"
      className="quiet-room z-60 flex flex-col"
    >
      <LampCone />
      <div className="relative z-10 flex items-center gap-3 px-4 py-3 sm:px-8">
        <Button
          type="button"
          variant="ghost"
          size="icon"
          className="quiet-ghost"
          onClick={onClose}
          aria-label="Keluar dari ruang sunyi (Esc)"
        >
          <Minimize2 aria-hidden="true" />
        </Button>
        <div className="min-w-0 pr-20 text-sm text-(--room-ink-soft) sm:pr-0">{header}</div>
      </div>
      <div className="relative z-10 mx-auto flex min-h-0 w-full max-w-3xl flex-1 flex-col px-4 pb-2 sm:px-8">{children}</div>
      {footer ? <div className="relative z-10 mx-auto w-full max-w-3xl px-4 py-4 sm:px-8">{footer}</div> : null}
      <LampHead />
    </div>,
    document.body,
  );
}
