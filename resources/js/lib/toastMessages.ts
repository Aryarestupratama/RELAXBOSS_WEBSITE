import type { ToastTone } from '@/lib/toast';

/** Prop bersama `flash` (HandleInertiaRequests): `id` unik per respons, `status` adalah kode dari `->with('status', ...)`. */
export type FlashProp = { id: string; status: string };

export type SharedFlashProps = {
  flash?: FlashProp | null;
};

type FlashMessage = { tone: ToastTone; text: string };

/**
 * Bank teks toast untuk kode status dari server (Design bagian 8). Kode yang tidak ada di sini (mis. `password-reset`,
 * `reset-link-sent`, `verification-link-sent`) tetap tampil sebagai Alert di tempatnya karena pengguna perlu membacanya lama.
 */
export const FLASH_MESSAGES: Record<string, FlashMessage> = {
  'mood-saved': { tone: 'success', text: 'Catatanmu tersimpan.' },
  'profile-updated': { tone: 'success', text: 'Profilmu tersimpan.' },
  'password-updated': { tone: 'success', text: 'Kata sandi diganti. Perangkat lain otomatis keluar.' },
  'avatar-updated': { tone: 'success', text: 'Foto profil diganti.' },
  'avatar-removed': { tone: 'success', text: 'Foto profil dihapus.' },
  'article-created': { tone: 'success', text: 'Artikel dibuat. Kamu bisa melanjutkan mengubahnya di sini.' },
  'article-updated': { tone: 'success', text: 'Perubahan disimpan.' },
  'assessment-created': { tone: 'success', text: 'Asesmen dibuat. Kamu bisa melanjutkan mengubahnya di sini.' },
  'assessment-updated': { tone: 'success', text: 'Perubahan disimpan.' },
  'user-activated': { tone: 'success', text: 'Akun diaktifkan kembali.' },
  'user-deactivated': { tone: 'success', text: 'Akun dinonaktifkan. Sesinya langsung diakhiri.' },
  'admin-created': { tone: 'success', text: 'Akun admin dibuat. Tautan atur kata sandi dikirim ke emailnya.' },
};
