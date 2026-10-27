import { useEffect, useSyncExternalStore } from 'react';
import { usePage } from '@inertiajs/react';
import { CircleAlert, CircleCheck, Info } from 'lucide-react';
import { Toast, ToastClose, ToastDescription, ToastProvider, ToastViewport } from '@/Components/ui/toast';
import { getToasts, subscribeToasts, toast, type ToastTone } from '@/lib/toast';
import { FLASH_MESSAGES, type SharedFlashProps } from '@/lib/toastMessages';

const ICONS: Record<ToastTone, { Icon: typeof Info; className: string }> = {
  success: { Icon: CircleCheck, className: 'text-success' },
  error: { Icon: CircleAlert, className: 'text-destructive' },
  info: { Icon: Info, className: 'text-primary' },
};

/** Id flash yang sudah ditampilkan, supaya riwayat peramban (tombol kembali) dan StrictMode tidak memunculkannya lagi. */
const shownFlash = new Set<string>();

/** Menampilkan `->with('status', kode)` dari server sebagai toast, sekali per respons. */
function useFlashToast(): void {
  const flash = usePage<SharedFlashProps>().props.flash ?? null;

  useEffect(() => {
    if (!flash || shownFlash.has(flash.id)) {
      return;
    }

    const message = FLASH_MESSAGES[flash.status];
    if (!message) {
      return;
    }

    shownFlash.add(flash.id);
    toast[message.tone](message.text, { id: `flash-${flash.id}` });
  }, [flash]);
}

/**
 * Satu-satunya tempat toast dirender (CMP-013). Dipasang di AppShell, AdminShell, dan GuestLayout.
 * Kanan atas di desktop, tengah atas (selebar layar) di HP. Sukses dan info bersuara sopan (`background`),
 * galat langsung dibacakan (`foreground`). F8 memindahkan fokus ke toast.
 */
export default function Toaster() {
  const items = useSyncExternalStore(subscribeToasts, getToasts, getToasts);
  useFlashToast();

  return (
    <ToastProvider label="Notifikasi" swipeDirection="right">
      {items.map((item) => {
        const { Icon, className } = ICONS[item.tone];

        return (
          <Toast
            key={item.id}
            tone={item.tone}
            type={item.tone === 'error' ? 'foreground' : 'background'}
            duration={item.duration}
            open={item.open}
            onOpenChange={(open) => {
              if (!open) {
                toast.dismiss(item.id);
              }
            }}
          >
            <Icon className={`mt-0.5 size-4 shrink-0 ${className}`} aria-hidden="true" />
            <ToastDescription>{item.message}</ToastDescription>
            <ToastClose />
          </Toast>
        );
      })}
      <ToastViewport />
    </ToastProvider>
  );
}
