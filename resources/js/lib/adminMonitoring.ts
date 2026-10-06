/** Tipe dan pemformat untuk Monitoring admin (SCR-022). Semua data sudah tanpa identitas dari server. */

export type ChatFilter = 'krisis' | 'rendah' | 'acak';
export type AttemptFilter = 'ai' | 'semua';

export type ConversationRow = {
  id: string;
  pseudo_id: string;
  date: string;
  total_turns: number;
  initial_intent: string | null;
  has_crisis: boolean;
  lowest_confidence: number | null;
  low_confidence: boolean;
};

export type AttemptRow = {
  id: number;
  pseudo_id: string;
  date: string;
  assessment: string;
  has_ai_recommendation: boolean;
};

export type MonitoringMessage = {
  id: number;
  role: 'user' | 'assistant';
  content: string;
  intent: string | null;
  confidence: number | null;
  low_confidence: boolean;
  is_crisis: boolean;
  suggestion: string | null;
};

export type SubScaleResult = {
  name: string;
  score: number;
  interpretation: string;
  severity: string;
};

/** Tanggal WIB (Y-m-d) dari server menjadi "5 Okt 2026". Hanya tanggal, tanpa jam. */
export function formatDate(value: string): string {
  const [year, month, day] = value.split('-').map(Number);
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return `${day} ${months[month - 1] ?? ''} ${year}`;
}
