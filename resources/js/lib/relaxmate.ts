export type SuggestionKind = 'escalate_crisis' | 'recommend_professional' | 'recommend_support';

export type ChatSuggestion = {
  type: SuggestionKind;
  priority: number;
  dismissible: boolean;
};

export type ChatMessage = {
  /** Angka dari server; string `tmp-...` untuk pesan yang baru dikirim dan belum dijawab. */
  id: number | string;
  role: 'user' | 'assistant';
  content: string;
  time: string;
  is_crisis: boolean;
  suggestion: ChatSuggestion | null;
  pending?: boolean;
};

export type SendResponse = {
  user_message: ChatMessage;
  assistant_message: ChatMessage;
  has_crisis: boolean;
  remaining_today: number;
};

/** Bank teks Design (SCR-017). */
export const RELAXMATE_INTRO =
  'Teman bicara berbasis AI. Bukan psikolog atau layanan medis. Ceritakan apa yang kamu rasakan, kapan saja.';
export const RELAXMATE_NOTE = 'RelaxMate bukan psikolog atau layanan medis.';
export const RELAXMATE_EMPTY = 'Belum ada percakapan. Mulai bercerita kapan pun kamu siap.';
export const RELAXMATE_TYPING = 'RelaxMate sedang menulis...';
export const RELAXMATE_UNANSWERED = 'Pesan terakhirmu belum dibalas. Pesanmu tetap tersimpan.';
export const RELAXMATE_CONSENT_NEEDED = 'Kamu perlu menyetujui penggunaan AI dulu sebelum bercerita.';
export const RELAXMATE_DAILY_LIMIT =
  'Kamu sudah mencapai batas percakapan hari ini. Besok kamu bisa lanjut bercerita. Kalau kamu butuh bantuan sekarang, lihat halaman Konsultasi Profesional.';

export const SUGGESTION_TEXT: Record<SuggestionKind, string> = {
  escalate_crisis:
    'Kamu tidak sendirian. Kontak bantuan ada di bagian atas halaman ini, dan kamu bisa membuka halaman Konsultasi Profesional.',
  recommend_professional:
    'Kalau terasa berat atau sudah berlangsung lama, bicara dengan psikolog atau tenaga profesional bisa sangat membantu.',
  recommend_support:
    'Kamu bisa mencatat perasaanmu di Mood Tracker atau mengerjakan Asesmen untuk mengenali kondisimu.',
};
