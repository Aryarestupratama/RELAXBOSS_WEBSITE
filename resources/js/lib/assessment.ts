export type SeverityLevel = 'normal' | 'mild' | 'moderate' | 'severe';

/** Penjelasan awam per tingkat (FR-008). Nada mengajak, tidak mengancam (Design SCR-014). */
export const SEVERITY_EXPLANATION: Record<SeverityLevel, string> = {
  normal: 'Kondisimu berada di rentang yang umum. Tetap perhatikan dirimu, ya.',
  mild: 'Ada tanda ringan yang layak diperhatikan. Memberi ruang untuk istirahat bisa membantu.',
  moderate:
    'Tandanya cukup terasa. Pertimbangkan bercerita kepada orang yang kamu percaya atau tenaga profesional.',
  severe:
    'Tandanya cukup berat. Kamu tidak harus menghadapinya sendirian; berbicara dengan tenaga profesional bisa sangat membantu.',
};

/** Bank teks Design: "Disclaimer Asesmen" (satu kalimat). `LEAD` dipakai bila kalimat diakhiri tautan. */
export const ASSESSMENT_DISCLAIMER_LEAD = 'Hasil ini bukan diagnosis; untuk penilaian yang tepat, ';
export const ASSESSMENT_DISCLAIMER = `${ASSESSMENT_DISCLAIMER_LEAD}bicarakan dengan psikolog atau tenaga profesional.`;

export function severityOf(value: string): SeverityLevel {
  return value === 'mild' || value === 'moderate' || value === 'severe' ? value : 'normal';
}

/** Bank teks Design: Rekomendasi AI (SCR-014, FR-010). */
export const AI_REC_TITLE = 'Rekomendasi untukmu';
export const AI_REC_LABEL = 'Disusun oleh AI';
export const AI_REC_SUMMARY_TITLE = 'Ringkasan';
export const AI_REC_LOADING = 'Menyusun rekomendasi untukmu...';
export const AI_REC_NEEDS_CONSENT =
  'Rekomendasi AI butuh persetujuanmu terlebih dulu. Sementara itu, ini rekomendasi umum untuk hasilmu.';
export const AI_REC_ENABLE = 'Aktifkan rekomendasi AI';
export const AI_REC_WAITING_CONTEXT =
  'Rekomendasi AI akan disusun setelah kamu menjawab atau melewati pertanyaan konteks. Sementara itu, ini rekomendasi umum untuk hasilmu.';
export const AI_REC_RETRY = 'Coba lagi';
export const AI_REC_FALLBACK_ERROR =
  'Rekomendasi AI sedang tidak tersedia. Sementara itu, ini rekomendasi umum untuk hasilmu.';
