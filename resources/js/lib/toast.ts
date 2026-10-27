/**
 * Toast RelaxBoss (Design CMP-013). Store kecil di luar React agar `toast.success()` bisa dipanggil dari mana saja.
 * Hanya teks singkat tanpa data sensitif (RULE-040): jangan masukkan isi pesan, catatan mood, atau email.
 * Dirender oleh `Components/shared/Toaster.tsx` (Radix Toast, tanpa menyuntik <style> sehingga lolos CSP).
 */
export type ToastTone = 'success' | 'error' | 'info';

export type ToastItem = {
  id: string;
  tone: ToastTone;
  message: string;
  /** Milidetik sebelum otomatis menghilang. */
  duration: number;
  open: boolean;
};

type ToastOptions = {
  /** Id yang sama tidak menampilkan toast ganda selama toast itu masih terbuka. */
  id?: string;
  duration?: number;
};

const MAX_VISIBLE = 3;
/** Jeda menunggu animasi tutup selesai sebelum toast dilepas dari daftar. */
const REMOVE_DELAY_MS = 300;
const DURATION: Record<ToastTone, number> = { success: 4000, info: 5000, error: 8000 };

let items: readonly ToastItem[] = [];
let counter = 0;
const listeners = new Set<() => void>();

function emit(): void {
  listeners.forEach((listener) => listener());
}

function add(tone: ToastTone, message: string, options?: ToastOptions): string {
  const id = options?.id ?? `toast-${++counter}`;

  if (items.some((item) => item.id === id && item.open)) {
    return id;
  }

  const next: ToastItem = { id, tone, message, duration: options?.duration ?? DURATION[tone], open: true };
  items = [...items.filter((item) => item.id !== id), next].slice(-MAX_VISIBLE);
  emit();

  return id;
}

function dismiss(id: string): void {
  if (!items.some((item) => item.id === id && item.open)) {
    return;
  }

  items = items.map((item) => (item.id === id ? { ...item, open: false } : item));
  emit();

  window.setTimeout(() => {
    items = items.filter((item) => item.id !== id);
    emit();
  }, REMOVE_DELAY_MS);
}

export const toast = {
  success: (message: string, options?: ToastOptions) => add('success', message, options),
  error: (message: string, options?: ToastOptions) => add('error', message, options),
  info: (message: string, options?: ToastOptions) => add('info', message, options),
  dismiss,
};

export function subscribeToasts(listener: () => void): () => void {
  listeners.add(listener);

  return () => {
    listeners.delete(listener);
  };
}

export function getToasts(): readonly ToastItem[] {
  return items;
}
