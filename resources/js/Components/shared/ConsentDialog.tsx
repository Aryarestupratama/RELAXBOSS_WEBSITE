import { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
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
    <Dialog
      ref={dialogRef}
      aria-labelledby="consent-title"
      aria-describedby="consent-desc"
      onCancel={(event) => {
        event.preventDefault();
        if (!processing) {
          onDecline();
        }
      }}
      className="max-w-lg"
    >
      <DialogHeader>
        <Badge variant="lavender" className="mb-1">
          <ShieldCheck aria-hidden="true" />
          Langkah {step} dari 2
        </Badge>
        <DialogTitle id="consent-title" ref={headingRef} tabIndex={-1}>
          {step === 1 ? CONSENT_AI_TITLE : CONSENT_TRAINING_QUESTION}
        </DialogTitle>
        <DialogDescription id="consent-desc">{step === 1 ? CONSENT_AI_TEXT : CONSENT_TRAINING_TEXT}</DialogDescription>
      </DialogHeader>

      {error ? (
        <Alert variant="destructive" className="mt-4">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      ) : null}

      <DialogFooter>
        <Button variant="ghost" className="rounded-full! px-5 text-sm" disabled={processing} onClick={onDecline}>
          Nanti saja
        </Button>
        {step === 1 ? (
          <Button className="rounded-full! px-5 text-sm" disabled={processing} onClick={agree}>
            Setuju dan lanjut
          </Button>
        ) : (
          <>
            <Button variant="outline" className="rounded-full! px-5 text-sm" disabled={processing} onClick={() => choose('denied')}>
              Tidak, terima kasih
            </Button>
            <Button variant="outline" className="rounded-full! px-5 text-sm" disabled={processing} onClick={() => choose('granted')}>
              Ya, boleh
            </Button>
          </>
        )}
      </DialogFooter>
    </Dialog>
  );
}
