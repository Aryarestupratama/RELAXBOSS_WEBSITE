import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@/Layouts/AppShell';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import { Button } from '@/Components/ui/button';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';

/**
 * SEMENTARA (TASK-019), hanya lokal: tempat menguji ConsentDialog sebelum RelaxMate (TASK-020) ada.
 * HAPUS saat TASK-020 beserta route `/app/uji/*` di routes/web.php.
 */
export default function UjiConsent() {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const [open, setOpen] = useState(needsConsent(consent));

  return (
    <AppShell title="Uji consent (sementara)">
      <h1 className="text-2xl font-semibold">Uji consent (sementara)</h1>
      <p className="mt-2 text-text-secondary">Halaman ini hanya ada di lokal dan akan dihapus di TASK-020.</p>

      <pre className="mt-4 overflow-x-auto rounded-lg border border-border bg-card p-4 text-sm">
        {JSON.stringify(consent, null, 2)}
      </pre>

      <div className="mt-4">
        <Button onClick={() => setOpen(true)}>Buka dialog consent</Button>
      </div>

      <ConsentDialog open={open} onDecline={() => router.visit('/dashboard')} onComplete={() => setOpen(false)} />
    </AppShell>
  );
}
