/** Tipe dan konstanta Mood Tracker (SCR-016). Label mengikuti Design.md dan enum `MoodLevel`. */

export type MoodValue = 1 | 2 | 3 | 4 | 5;
export type ArousalValue = 'energized' | 'tired';

export const MOOD_LABELS: Record<MoodValue, string> = {
  1: 'Sangat sedih',
  2: 'Sedih',
  3: 'Biasa',
  4: 'Senang',
  5: 'Luar biasa',
};

export const AROUSAL_LABELS: Record<ArousalValue, string> = {
  energized: 'Bertenaga',
  tired: 'Lelah',
};

export type MoodEntryItem = {
  id: number;
  /** Jam WIB, "HH:mm". */
  time: string;
  /** Menit sejak 00:00 WIB (0 sampai 1439), untuk posisi di grafik hari. */
  minutes: number;
  mood: number;
  mood_label: string;
  arousal: string | null;
  arousal_label: string | null;
  note: string | null;
};

export type MoodDay = {
  /** Tanggal WIB, "Y-m-d". */
  date: string;
  /** Mis. "5 Okt". */
  label: string;
  is_today: boolean;
  /** Rata-rata hari itu; null bila tidak ada entri (celah, bukan nol). */
  average: number | null;
  entries: MoodEntryItem[];
};

/** Rata-rata ditampilkan dengan koma desimal Indonesia, mis. 3,5. */
export function formatAverage(value: number): string {
  return String(Math.round(value * 10) / 10).replace('.', ',');
}
