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
 * CMP-049. Ruang sunyi: kertas memenuhi layar tanpa rail, bilah, dan kartu; hanya kertas, tanggal, dan satu tombol keluar.
 * Memakai portal ke `body`, mematikan semua yang ada di belakangnya (`inert`) supaya fokus dan pembaca layar tetap di dalam,
 * mengunci gulir halaman, keluar dengan Esc, dan mengembalikan fokus ke elemen yang membukanya.
 */
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
      className="fixed inset-0 z-60 flex flex-col bg-background"
    >
      <div className="flex items-center justify-between gap-4 px-4 py-3 sm:px-8">
        <div className="min-w-0 text-sm text-text-secondary">{header}</div>
        <Button type="button" variant="ghost" size="icon" onClick={onClose} aria-label="Keluar dari ruang sunyi (Esc)">
          <Minimize2 aria-hidden="true" />
        </Button>
      </div>
      <div className="mx-auto flex min-h-0 w-full max-w-3xl flex-1 flex-col px-4 sm:px-8">{children}</div>
      {footer ? <div className="mx-auto w-full max-w-3xl px-4 py-4 sm:px-8">{footer}</div> : null}
    </div>,
    document.body,
  );
}
