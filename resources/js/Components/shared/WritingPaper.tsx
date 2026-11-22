import { useCallback, useEffect, useLayoutEffect, useRef, useState, type ChangeEvent, type KeyboardEvent } from 'react';
import { cn } from '@/lib/utils';
import { ANCHOR_RATIO, measureCaret, riseTarget } from '@/lib/writing';

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

function prefersReducedMotion(): boolean {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * CMP-048. Kertas bergaris untuk menulis catatan. Textarea asli dipertahankan (keyboard HP, IME, undo, salin-tempel,
 * pembaca layar) dengan kursor bawaan disembunyikan; gantinya kotak kecil bercahaya yang bergeser mengikuti huruf
 * terakhir (posisinya diukur dari salinan tak terlihat), sorotan ke atas, dan lampu meja pada baris aktif.
 * Kertas "naik" seperti mesin tik: begitu tulisan turun melewati jangkar, gulir mengikuti satu baris demi satu baris.
 * Pengguna dengan `prefers-reduced-motion` mendapat posisi yang sama tanpa transisi dan tanpa gulir halus.
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
  const follow = useRef(false);
  const [focused, setFocused] = useState(false);

  /** Menyesuaikan tinggi textarea dengan isi, memindahkan kotak dan lampu, dan (bila `rise`) menaikkan kertas. */
  const sync = useCallback((rise: boolean) => {
    const text = textRef.current;
    const mirror = mirrorRef.current;
    const sheet = sheetRef.current;
    const viewport = viewportRef.current;
    if (!text || !mirror || !sheet || !viewport) {
      return;
    }

    text.style.height = 'auto';
    text.style.height = `${text.scrollHeight}px`;
    // Ruang di bawah tulisan supaya baris terakhir bisa naik sampai jangkar.
    sheet.style.paddingBottom = `${viewport.clientHeight * (1 - ANCHOR_RATIO)}px`;

    const point = measureCaret(text, mirror);
    sheet.style.setProperty('--writing-caret-x', `${point.x}px`);
    sheet.style.setProperty('--writing-caret-y', `${point.y}px`);

    if (!rise) {
      return;
    }
    const lineHeight = Number.parseFloat(getComputedStyle(text).lineHeight);
    if (Number.isNaN(lineHeight)) {
      return;
    }
    const target = riseTarget({
      lineTop: point.y,
      lineHeight,
      scrollTop: viewport.scrollTop,
      viewportHeight: viewport.clientHeight,
    });
    if (target !== null) {
      viewport.scrollTo({ top: target, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    }
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

  // Setelah isi berubah (diketik atau diatur dari luar): ukur ulang; kertas naik hanya bila karena mengetik.
  useLayoutEffect(() => {
    sync(follow.current);
    follow.current = false;
  }, [value, sync]);

  // Lebar atau font berubah (ukuran jendela, font tulisan tangan selesai dimuat): ukur ulang tanpa menggulir.
  useEffect(() => {
    const viewport = viewportRef.current;
    if (!viewport) {
      return;
    }
    const observer = new ResizeObserver(() => sync(false));
    observer.observe(viewport);
    void document.fonts.ready.then(() => sync(false));
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
    follow.current = true;
    wake();
    onChange(event.target.value);
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
      className={cn(
        'relative overflow-y-auto overscroll-contain rounded-2xl border-[1.5px] bg-card focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-ring',
        invalid ? 'border-destructive' : 'border-border',
        className,
      )}
    >
      <div
        ref={sheetRef}
        data-quiet={quiet}
        data-focused={focused}
        className="writing-sheet"
      >
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
          onKeyUp={() => sync(true)}
          onClick={() => sync(true)}
          onSelect={() => sync(false)}
          onFocus={() => {
            setFocused(true);
            wake();
            sync(false);
          }}
          onBlur={() => setFocused(false)}
        />
        <span ref={caretRef} aria-hidden="true" data-idle="false" className="writing-caret" />
      </div>
    </div>
  );
}
