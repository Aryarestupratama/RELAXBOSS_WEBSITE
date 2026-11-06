/** Bentuk prop bersama `consent` (HandleInertiaRequests). `null` bila belum login. */
export type ConsentTexts = {
  ai_title: string;
  ai_text: string;
  training_question: string;
  training_text: string;
};

export type ConsentState = {
  ai_granted: boolean;
  training_choice: 'granted' | 'denied' | null;
  training_answered: boolean;
  complete: boolean;
  texts: ConsentTexts;
};

export type SharedConsentProps = {
  consent?: ConsentState | null;
};

/** Pengguna perlu membuka dialog bila salah satu langkah belum selesai. */
export function needsConsent(consent: ConsentState | null | undefined): boolean {
  return consent != null && !consent.complete;
}

/** Teks consent datang dari server (config `relaxboss.consent`, sumber tunggal bersama API mobile) lewat prop `consent.texts`. */
export const CONSENT_ERROR = 'Ada yang tidak berjalan semestinya. Coba lagi, ya.';
