import {
  useCallback,
  useEffect,
  useLayoutEffect,
  useRef,
  useState,
  type ChangeEvent,
  type KeyboardEvent,
  type MouseEvent,
  type PointerEvent,
  type UIEvent,
} from 'react';
import { cn } from '@/lib/utils';
import { ANCHOR_RATIO, measureCaret, paperOffset } from '@/lib/writing';

type WritingPaperProps = {
  value: string;
  onChange: (value: string) => void;
  maxLength: number;
  ariaLabel: string;
  placeholder: string;
  disabled?: boolean;
  autoFocus?: boolean;
  invalid?: boolean;
  describedBy?: string;
  /** Ruang sunyi: lampu lebih redup di luar baris aktif. */
  quiet?: boolean;
  /** Ctrl atau Cmd + Enter. */
  onSubmitShortcut?: () => void;
  /** Tinggi kertas yang terlihat (kelas Tailwind), mis. `h-[min(34rem,60dvh)]`. */
  className?: string;
};

/** Menghentikan napas kotak saat mengetik, lalu mengembalikannya setelah diam sebentar. */
const IDLE_MS = 1400;

/** Geseran jari yang baru dianggap menggeser kertas (di bawahnya dianggap ketukan), dalam piksel. */
const DRAG_SLOP = 6;

/** Lama transisi kertas dimatikan setelah geseran terakhir, supaya kertas mengikuti jari tanpa tertinggal. */
const PAN_SETTLE_MS = 120;

/** Cara `sync` memperlakukan geseran manual: `follow` mengikuti kursor (membatalkannya), `pin` menahan posisi kertas, `hold` mempertahankan selisihnya. */
type SyncMode = 'follow' | 'pin' | 'hold';

/**
 * CMP-048. Kertas bergaris untuk menulis catatan, bergaya mesin tik. Textarea asli dipertahankan (keyboard HP, IME, undo,
 * salin-tempel, pembaca layar) dengan kursor bawaan disembunyikan. Kertas tidak punya batang gulir: baris aktif berhenti di
 * jangkar dan kertas yang naik sendiri (transform) setiap tulisan turun satu baris. Pencetak huruf (`.writing-caret`) ada
 * di bawah baris aktif, tepat di tempat huruf berikutnya dicetak (di depan huruf terakhir, bukan di atasnya), tanpa cahaya. Panah keyboard atau ketukan yang memindahkan kursor ke baris
 * lain membawa kertas turun atau naik mengikutinya. `quiet` menggelapkan latar kertas di luar cahaya lampu pojok
 * (dipasang oleh `QuietRoom`). Pengguna dengan `prefers-reduced-motion` mendapat posisi yang sama tanpa transisi.
 * Kertas yang sudah naik tetap bisa dibaca ulang: roda mouse dan geseran jari (layar sentuh) menggesernya; mengetik, panah
 * keyboard, atau mengetuk bagian kosong mengembalikannya mengikuti kursor.
 * Gaya di `resources/css/writing.css`; isi tidak pernah disimpan di luar state induk.
 */
export default function WritingPaper({
  value,
  onChange,
  maxLength,
  ariaLabel,
  placeholder,
  disabled = false,
  autoFocus = false,
  invalid = false,
  describedBy,
  quiet = false,
  onSubmitShortcut,
  className,
}: WritingPaperProps) {
  const viewportRef = useRef<HTMLDivElement>(null);
  const sheetRef = useRef<HTMLDivElement>(null);
  const textRef = useRef<HTMLTextAreaElement>(null);
  const mirrorRef = useRef<HTMLDivElement>(null);
  const caretRef = useRef<HTMLSpanElement>(null);
  const idleTimer = useRef<number | undefined>(undefined);
  const panTimer = useRef<number | undefined>(undefined);
  /** Posisi kertas: naik otomatis (`auto`), selisih geseran pengguna (`pan`), dan hasilnya (`total`), serta batas atasnya. */
  const autoRef = useRef(0);
  const panRef = useRef(0);
  const totalRef = useRef(0);
  const maxRef = useRef(0);
  const drag = useRef<{ id: number; lastY: number; startY: number; active: boolean } | null>(null);
  const [focused, setFocused] = useState(false);

  /** Menyesuaikan tinggi textarea dengan isi, memindahkan pencetak huruf dan lampu, dan menaikkan kertas ke jangkar. */
  const sync = useCallback((mode: SyncMode = 'follow') => {
    const text = textRef.current;
    const mirror = mirrorRef.current;
    const sheet = sheetRef.current;
    const viewport = viewportRef.current;
    if (!text || !mirror || !sheet || !viewport) {
      return;
    }

    const viewportHeight = viewport.clientHeight;
    text.style.height = 'auto';
    text.style.height = `${text.scrollHeight}px`;
    // Kertas selalu menutup bagian yang terlihat, juga saat baris terakhir sudah naik ke jangkar.
    sheet.style.minHeight = `${viewportHeight}px`;
    sheet.style.paddingBottom = `${viewportHeight * (1 - ANCHOR_RATIO)}px`;

    const point = measureCaret(text, mirror);
    sheet.style.setProperty('--writing-caret-x', `${point.x}px`);
    sheet.style.setProperty('--writing-caret-y', `${point.y}px`);

    const offset = paperOffset({ lineTop: point.y, viewportHeight });
    const maxTotal = Math.max(0, sheet.offsetHeight - viewportHeight);
    autoRef.current = offset;
    maxRef.current = maxTotal;
    if (mode === 'follow') {
      panRef.current = 0;
    } else if (mode === 'pin') {
      panRef.current = totalRef.current - offset;
    }
    const total = Math.min(Math.max(offset + panRef.current, 0), maxTotal);
    panRef.current = total - offset;
    totalRef.current = total;
    sheet.style.transform = `translate3d(0, ${-total}px, 0)`;
  }, []);

  /** Menggeser kertas atas permintaan pengguna (roda atau jari). `true` bila kertas benar-benar bergerak. */
  const panBy = useCallback((delta: number): boolean => {
    const sheet = sheetRef.current;
    if (!sheet) {
      return false;
    }
    const next = Math.min(Math.max(totalRef.current + delta, 0), maxRef.current);
    if (next === totalRef.current) {
      return false;
    }
    totalRef.current = next;
    panRef.current = next - autoRef.current;
    sheet.dataset.panning = 'true';
    sheet.style.transform = `translate3d(0, ${-next}px, 0)`;
    window.clearTimeout(panTimer.current);
    panTimer.current = window.setTimeout(() => {
      delete sheet.dataset.panning;
    }, PAN_SETTLE_MS);
    return true;
  }, []);

  const wake = useCallback(() => {
    const caret = caretRef.current;
    if (!caret) {
      return;
    }
    caret.dataset.idle = 'false';
    window.clearTimeout(idleTimer.current);
    idleTimer.current = window.setTimeout(() => {
      caret.dataset.idle = 'true';
    }, IDLE_MS);
  }, []);

  // Setelah isi berubah (diketik atau diatur dari luar): ukur ulang dan naikkan kertas.
  useLayoutEffect(() => {
    sync('follow');
  }, [value, sync]);

  // Lebar atau font berubah (ukuran jendela, font tulisan tangan selesai dimuat): ukur ulang tanpa menggulir.
  useEffect(() => {
    const viewport = viewportRef.current;
    if (!viewport) {
      return;
    }
    const observer = new ResizeObserver(() => sync('hold'));
    observer.observe(viewport);
    void document.fonts.ready.then(() => sync('hold'));
    return () => observer.disconnect();
  }, [sync]);

  // Roda mouse atau trackpad menggeser kertas. Pendengar non-pasif supaya halaman tidak ikut bergulir saat kertas bergerak;
  // di ujung kertas gulir diteruskan ke halaman.
  useEffect(() => {
    const viewport = viewportRef.current;
    if (!viewport) {
      return;
    }
    const onWheel = (event: WheelEvent) => {
      if (event.ctrlKey) {
        return;
      }
      const lineHeight = Number.parseFloat(getComputedStyle(viewport).getPropertyValue('--writing-line')) || 32;
      const unit = event.deltaMode === 1 ? lineHeight : event.deltaMode === 2 ? viewport.clientHeight : 1;
      if (panBy(event.deltaY * unit)) {
        event.preventDefault();
      }
    };
    viewport.addEventListener('wheel', onWheel, { passive: false });
    return () => viewport.removeEventListener('wheel', onWheel);
  }, [panBy]);

  useEffect(() => {
    if (!autoFocus) {
      return;
    }
    const text = textRef.current;
    if (text) {
      text.focus();
      text.setSelectionRange(text.value.length, text.value.length);
    }
  }, [autoFocus]);

  useEffect(
    () => () => {
      window.clearTimeout(idleTimer.current);
      window.clearTimeout(panTimer.current);
    },
    [],
  );

  const handleChange = (event: ChangeEvent<HTMLTextAreaElement>) => {
    wake();
    onChange(event.target.value);
  };

  // Kertas tidak boleh bergulir sendiri (browser kadang menggulir wadah demi kursor); posisinya hanya diatur `sync`.
  const handleScroll = (event: UIEvent<HTMLDivElement>) => {
    const viewport = event.currentTarget;
    viewport.scrollTop = 0;
    viewport.scrollLeft = 0;
  };

  // Mengetuk bagian kertas yang kosong (di bawah tulisan atau di tepi) memfokuskan kolom dan menaruh kursor di akhir.
  const handleViewportMouseDown = (event: MouseEvent<HTMLDivElement>) => {
    const text = textRef.current;
    if (!text || disabled || event.target === text) {
      return;
    }
    event.preventDefault();
    text.focus();
    text.setSelectionRange(text.value.length, text.value.length);
    sync('follow');
  };

  // Jari atau pena menggeser kertas ke atas-bawah; mouse memakai roda dan seleksi biasa. Ketukan (geser di bawah ambang) tetap
  // menaruh kursor seperti biasa.
  const handlePointerDown = (event: PointerEvent<HTMLDivElement>) => {
    if (event.pointerType === 'mouse') {
      return;
    }
    drag.current = { id: event.pointerId, lastY: event.clientY, startY: event.clientY, active: false };
  };

  const handlePointerMove = (event: PointerEvent<HTMLDivElement>) => {
    const current = drag.current;
    if (!current || current.id !== event.pointerId) {
      return;
    }
    if (!current.active) {
      if (Math.abs(event.clientY - current.startY) < DRAG_SLOP) {
        return;
      }
      current.active = true;
      event.currentTarget.setPointerCapture(event.pointerId);
    }
    panBy(current.lastY - event.clientY);
    current.lastY = event.clientY;
  };

  const handlePointerEnd = (event: PointerEvent<HTMLDivElement>) => {
    if (drag.current?.id === event.pointerId) {
      drag.current = null;
    }
  };

  const handleKeyDown = (event: KeyboardEvent<HTMLTextAreaElement>) => {
    if (event.key === 'Enter' && (event.ctrlKey || event.metaKey) && onSubmitShortcut) {
      event.preventDefault();
      onSubmitShortcut();
    }
  };

  return (
    <div
      ref={viewportRef}
      data-quiet={quiet}
      data-focused={focused}
      onScroll={handleScroll}
      onMouseDown={handleViewportMouseDown}
      onPointerDown={handlePointerDown}
      onPointerMove={handlePointerMove}
      onPointerUp={handlePointerEnd}
      onPointerCancel={handlePointerEnd}
      className={cn(
        'writing-viewport focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-ring',
        quiet ? 'rounded-md' : cn('rounded-2xl border-[1.5px]', invalid ? 'border-destructive' : 'border-border'),
        className,
      )}
    >
      <div ref={sheetRef} className="writing-sheet">
        <div ref={mirrorRef} aria-hidden="true" className="writing-mirror" />
        <textarea
          ref={textRef}
          value={value}
          maxLength={maxLength}
          disabled={disabled}
          rows={1}
          spellCheck
          placeholder={placeholder}
          aria-label={ariaLabel}
          aria-invalid={invalid}
          aria-describedby={describedBy}
          className="writing-text"
          onChange={handleChange}
          onKeyDown={handleKeyDown}
          onKeyUp={(event) => {
            if (!['Shift', 'Control', 'Alt', 'Meta'].includes(event.key)) {
              sync('follow');
            }
          }}
          onClick={() => sync('pin')}
          onSelect={() => sync('pin')}
          onFocus={() => {
            setFocused(true);
            wake();
            sync('pin');
          }}
          onBlur={() => setFocused(false)}
        />
        <span ref={caretRef} aria-hidden="true" data-idle="false" className="writing-caret" />
      </div>
    </div>
  );
}
