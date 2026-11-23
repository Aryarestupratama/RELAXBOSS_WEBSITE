import { useCallback, useEffect, useLayoutEffect, useRef, useState, type ChangeEvent, type KeyboardEvent, type MouseEvent, type UIEvent } from 'react';
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

/**
 * CMP-048. Kertas bergaris untuk menulis catatan, bergaya mesin tik. Textarea asli dipertahankan (keyboard HP, IME, undo,
 * salin-tempel, pembaca layar) dengan kursor bawaan disembunyikan. Kertas tidak punya batang gulir: baris aktif berhenti di
 * jangkar dan kertas yang naik sendiri (transform) setiap tulisan turun satu baris. Pencetak huruf (`.writing-caret`) ada
 * di bawah baris aktif, tepat di tempat huruf berikutnya dicetak (di depan huruf terakhir, bukan di atasnya), dan lampunya
 * menyorot ke atas ke baris itu berbentuk segitiga terbalik. Panah keyboard atau ketukan yang memindahkan kursor ke baris
 * lain membawa kertas turun atau naik mengikutinya. `quiet` menggelapkan latar kertas di luar cahaya lampu pojok
 * (dipasang oleh `QuietRoom`). Pengguna dengan `prefers-reduced-motion` mendapat posisi yang sama tanpa transisi.
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
  const [focused, setFocused] = useState(false);

  /** Menyesuaikan tinggi textarea dengan isi, memindahkan pencetak huruf dan lampu, dan menaikkan kertas ke jangkar. */
  const sync = useCallback(() => {
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
    sheet.style.transform = `translate3d(0, ${-offset}px, 0)`;
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
    sync();
  }, [value, sync]);

  // Lebar atau font berubah (ukuran jendela, font tulisan tangan selesai dimuat): ukur ulang tanpa menggulir.
  useEffect(() => {
    const viewport = viewportRef.current;
    if (!viewport) {
      return;
    }
    const observer = new ResizeObserver(() => sync());
    observer.observe(viewport);
    void document.fonts.ready.then(() => sync());
    return () => observer.disconnect();
  }, [sync]);

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

  useEffect(() => () => window.clearTimeout(idleTimer.current), []);

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
    sync();
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
      className={cn(
        'writing-viewport focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-ring',
        quiet ? 'rounded-md' : cn('rounded-2xl border-[1.5px]', invalid ? 'border-destructive' : 'border-border'),
        className,
      )}
    >
      <div ref={sheetRef} className="writing-sheet">
        <div ref={mirrorRef} aria-hidden="true" className="writing-mirror" />
        <span aria-hidden="true" className="writing-lamp" />
        <span aria-hidden="true" className="writing-beam" />
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
          onKeyUp={() => sync()}
          onClick={() => sync()}
          onSelect={() => sync()}
          onFocus={() => {
            setFocused(true);
            wake();
            sync();
          }}
          onBlur={() => setFocused(false)}
        />
        <span ref={caretRef} aria-hidden="true" data-idle="false" className="writing-caret" />
      </div>
    </div>
  );
}
