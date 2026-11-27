/** Teks dan tipe untuk Ekspor Excel di Admin (TASK-057, SCR-022). */

export type ExportRange = 'all' | 'custom';

/** `all` = semua dataset yang mendukung Excel (dalam satu berkas, satu kelompok lembar per fitur). */
export type ExportDataset = string;

const DATASET_LABEL: Record<string, string> = {
  chat: 'Chatbot (RelaxMate)',
  assessment: 'Asesmen',
};

const DATASET_HINT: Record<string, string> = {
  chat: 'Satu baris per pesan, lengkap dengan intent dan penanda krisis.',
  assessment: 'Tiga lembar: ringkasan per hasil, skor per subskala, dan jawaban per butir.',
};

const RECORD_UNIT: Record<string, string> = {
  chat: 'percakapan',
  assessment: 'hasil asesmen',
};

export function datasetLabel(feature: string): string {
  return DATASET_LABEL[feature] ?? feature;
}

export function datasetHint(feature: string): string | null {
  return DATASET_HINT[feature] ?? null;
}

/** Header `X-Export-Counts` (`chat=12,assessment=5`) menjadi kalimat "12 percakapan, 5 hasil asesmen". */
export function describeCounts(header: string | null): string {
  if (!header) {
    return '';
  }

  return header
    .split(',')
    .map((pair) => {
      const [feature, count] = pair.split('=');
      return `${count} ${RECORD_UNIT[feature] ?? feature}`;
    })
    .join(', ');
}

export const EXPORT_NOTE =
  'Hanya pengguna yang menjawab "Ya" pada persetujuan pelatihan yang ikut diekspor. Tanpa nama, email, ID pengguna, jurusan, atau kampus, dan tanpa tanggal di isi lembar. Percakapan krisis ikut diekspor dan ditandai di kolom is_crisis.';

export const EXPORT_WARNING =
  'Berkas berisi isi sensitif. Simpan di tempat aman, jangan di-commit atau dibagikan, tinjau manual sebelum dipakai melatih model, dan hapus setelah dipakai. Setiap unduhan tercatat.';
