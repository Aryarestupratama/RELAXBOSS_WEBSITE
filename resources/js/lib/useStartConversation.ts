import { useState } from 'react';
import { router } from '@inertiajs/react';
import { needsConsent, type ConsentState } from '@/lib/consent';

/**
 * Memulai Percakapan baru (API-014). Bila consent belum lengkap, membuka dialog (aksi AI ditolak server tanpa consent).
 * Dipakai daftar dan halaman Percakapan.
 */
export function useStartConversation(
  consent: ConsentState | null,
  openConsent: () => void,
  /** Dipanggil sebelum permintaan dikirim (mis. maskot berjalan keluar); `go` melanjutkan permintaan. */
  beforeStart?: (go: () => void) => void,
) {
  const [starting, setStarting] = useState(false);

  const start = (): void => {
    if (starting) {
      return;
    }
    if (needsConsent(consent)) {
      openConsent();
      return;
    }
    const post = (): void =>
      router.post(
        '/app/relaxmate/percakapan',
        {},
        {
          onStart: () => setStarting(true),
          onFinish: () => setStarting(false),
        },
      );
    if (beforeStart) {
      beforeStart(post);
      return;
    }
    post();
  };

  return { starting, start };
}
