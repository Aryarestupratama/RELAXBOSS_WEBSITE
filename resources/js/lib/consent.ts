/** Bentuk prop bersama `consent` (HandleInertiaRequests). `null` bila belum login. */
export type ConsentState = {
  ai_granted: boolean;
  training_choice: 'granted' | 'denied' | null;
  training_answered: boolean;
  complete: boolean;
};

export type SharedConsentProps = {
  consent?: ConsentState | null;
};

/** Pengguna perlu membuka dialog bila salah satu langkah belum selesai. */
export function needsConsent(consent: ConsentState | null | undefined): boolean {
  return consent != null && !consent.complete;
}

/** Bank teks Design: "Consent AI". Bila versi teks berubah, naikkan `ai_consent_version` di config. */
export const CONSENT_AI_TITLE = 'Sebelum bercerita ke RelaxMate';
export const CONSENT_AI_TEXT =
  'RelaxMate adalah teman bicara berbasis AI, bukan psikolog atau layanan medis. Pesanmu dan jurusanmu (bila kamu isi) dikirim ke penyedia AI pihak ketiga untuk dibalas, tanpa nama, email, atau kampusmu. Pengelola RelaxBoss dapat meninjau isi percakapan tanpa identitasmu untuk menjaga kualitas dan keselamatan. Jangan bagikan data yang sangat pribadi seperti alamat atau nomor identitas. Dengan melanjutkan, kamu menyetujui hal ini.';

/** Bank teks Design: "Persetujuan pelatihan" (dipecah: pertanyaan dan penjelasan). Naikkan `ai_training_consent_version` bila berubah. */
export const CONSENT_TRAINING_QUESTION = 'Bolehkah percakapan dan hasil asesmenmu dipakai untuk melatih model AI di masa depan?';
export const CONSENT_TRAINING_TEXT =
  'Datanya diekspor tanpa nama, email, jurusan, dan kampus, dan termasuk percakapan yang menyentuh topik sensitif seperti menyakiti diri. Pilihanmu tidak memengaruhi akses ke fitur apa pun dan bisa kamu ubah kapan saja di pengaturan akun. Data yang sudah diekspor tidak bisa ditarik kembali.';

export const CONSENT_ERROR = 'Ada yang tidak berjalan semestinya. Coba lagi, ya.';
