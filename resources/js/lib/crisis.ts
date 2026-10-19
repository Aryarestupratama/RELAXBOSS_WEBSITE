/** Bentuk kontak bantuan dari prop bersama `crisisContacts` (HandleInertiaRequests, sumber: config `crisis_contacts`). */
export type CrisisContact = {
  name: string;
  phone: string | null;
  url: string | null;
  note: string | null;
  dummy: boolean;
};

/** Bank teks Design: "Crisis Response". Harus sama dengan `App\Support\CrisisResponse::TEXT`. */
export const CRISIS_RESPONSE_TEXT =
  'Aku mendengarmu, dan aku senang kamu mau bercerita. Kamu tidak harus menghadapi ini sendirian. Kalau kamu berpikir untuk menyakiti dirimu sendiri, tolong hubungi bantuan sekarang atau minta seseorang yang kamu percaya menemanimu.';

export const CRISIS_DUMMY_LABEL = 'Contoh sementara, bukan kontak yang sebenarnya.';

export const CRISIS_NO_CONTACTS = 'Daftar kontak sedang disiapkan.';

/**
 * Hanya angka dan plus untuk tautan tel:. Ekstensi ("119 ext 8") menjadi jeda koma ("119,8"),
 * supaya tidak terhubung ke nomor "1198".
 */
export function telHref(phone: string): string {
  const dial = phone.replace(/\s*(?:ext\.?|ekstensi)\s*(\d+)/i, ',$1');
  return `tel:${dial.replace(/[^0-9+,]/g, '')}`;
}

/** Tautan hanya dirender bila diawali https:// (Architecture 8). */
export function isSafeUrl(url: string | null): url is string {
  return typeof url === 'string' && url.startsWith('https://');
}
