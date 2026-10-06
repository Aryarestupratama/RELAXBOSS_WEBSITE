import { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import {
  CONSENT_AI_TEXT,
  CONSENT_AI_TITLE,
  CONSENT_ERROR,
  CONSENT_TRAINING_QUESTION,
  CONSENT_TRAINING_TEXT,
  type SharedConsentProps,
} from '@/lib/consent';

type ConsentDialogProps = {
  open: boolean;
  /** Pengguna menutup dialog tanpa menyelesaikan (tombol "Nanti saja" atau Esc). Biasanya kembali ke Dashboard. */
  onDecline: () => void;
  /** Kedua langkah selesai. */
  onComplete: () => void;
};

/**
 * CMP-014. Dua langkah: (1) consent AI, (2) persetujuan pelatihan.
 * Langkah mengikuti prop bersama `consent` dari server, jadi dialog yang dibuka ulang
 * langsung ke langkah yang belum selesai. Langkah 2 tanpa pilihan awal dan dua tombol setara (RULE-071).
 * Memakai <dialog> bawaan browser (fokus terkunci, Esc menutup, tanpa <style> yang disuntik; aman untuk CSP).
 */
export default function ConsentDialog({ open, onDecline, onComplete }: ConsentDialogProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const step: 1 | 2 = consent?.ai_granted ? 2 : 1;

  const dialogRef = useRef<HTMLDialogElement>(null);
  const headingRef = useRef<HTMLHeadingElement>(null);
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) {
      return;
    }
    if (open && !dialog.open) {
      dialog.showModal();
    }
    if (!open && dialog.open) {
      dialog.close();
    }
  }, [open]);

  // Fokus ke judul (bukan ke tombol pilihan) agar tidak ada pilihan yang tampak terpilih lebih dulu.
  useEffect(() => {
    if (open) {
      headingRef.current?.focus();
    }
  }, [open, step]);

  const post = (url: string, data: Record<string, string>, onDone?: () => void) => {
    setProcessing(true);
    setError(null);
    router.post(url, data, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => onDone?.(),
      onError: () => setError(CONSENT_ERROR),
      onNetworkError: () => setError(CONSENT_ERROR),
      onFinish: () => setProcessing(false),
    });
  };

  const agree = () => post('/app/relaxmate/consent', {});
  const choose = (choice: 'granted' | 'denied') =>
    post('/app/akun/persetujuan-pelatihan', { choice }, onComplete);

  return (
    <dialog
      ref={dialogRef}
      aria-labelledby="consent-title"
      aria-describedby="consent-desc"
      onCancel={(event) => {
        event.preventDefault();
        if (!processing) {
          onDecline();
        }
      }}
      className="m-auto max-h-[calc(100dvh-2rem)] w-[calc(100%-2rem)] max-w-lg overflow-y-auto rounded-xl border border-border bg-card p-6 text-card-foreground shadow-popover backdrop:bg-primary/40"
    >
      <p className="flex items-center gap-2 text-sm text-text-secondary">
        <ShieldCheck className="size-4 text-brand-strong" aria-hidden="true" />
        Langkah {step} dari 2
      </p>

      {step === 1 ? (
        <>
          <h2 id="consent-title" ref={headingRef} tabIndex={-1} className="mt-2 text-lg font-semibold outline-none">
            {CONSENT_AI_TITLE}
          </h2>
          <p id="consent-desc" className="mt-3 text-text-secondary">
            {CONSENT_AI_TEXT}
          </p>
        </>
      ) : (
        <>
          <h2 id="consent-title" ref={headingRef} tabIndex={-1} className="mt-2 text-lg font-semibold outline-none">
            {CONSENT_TRAINING_QUESTION}
          </h2>
          <p id="consent-desc" className="mt-3 text-text-secondary">
            {CONSENT_TRAINING_TEXT}
          </p>
        </>
      )}

      {error ? (
        <Alert variant="destructive" className="mt-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      ) : null}

      <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <Button variant="ghost" disabled={processing} onClick={onDecline}>
          Nanti saja
        </Button>
        {step === 1 ? (
          <Button disabled={processing} onClick={agree}>
            Setuju dan lanjut
          </Button>
        ) : (
          <>
            <Button variant="outline" disabled={processing} onClick={() => choose('denied')}>
              Tidak, terima kasih
            </Button>
            <Button variant="outline" disabled={processing} onClick={() => choose('granted')}>
              Ya, boleh
            </Button>
          </>
        )}
      </div>
    </dialog>
  );
}
