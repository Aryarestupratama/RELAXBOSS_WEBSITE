import { useState } from 'react';
import { router } from '@inertiajs/react';
import { needsConsent, type ConsentState } from '@/lib/consent';

/**
 * Memulai Percakapan baru (API-014). Bila consent belum lengkap, membuka dialog (aksi AI ditolak server tanpa consent).
 * Dipakai daftar dan halaman Percakapan.
 */
export function useStartConversation(consent: ConsentState | null, openConsent: () => void) {
  const [starting, setStarting] = useState(false);

  const start = (): void => {
    if (starting) {
      return;
    }
    if (needsConsent(consent)) {
      openConsent();
      return;
    }
    router.post(
      '/app/relaxmate/percakapan',
      {},
      {
        onStart: () => setStarting(true),
        onFinish: () => setStarting(false),
      },
    );
  };

  return { starting, start };
}
