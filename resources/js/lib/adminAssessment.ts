/** Tipe dan pembantu formulir Instrumen admin (SCR-020, FR-023). Tidak berisi data pengguna. */

export type OptionRow = { value: number | ''; label: string };

export type QuestionRow = {
  id: number | null;
  text: string;
  sub_scale: string;
  is_reversed: boolean;
};

export type RuleRow = {
  sub_scale: string;
  min_score: number | '';
  max_score: number | '';
  interpretation: string;
  severity_level: string;
  trigger_pfa: boolean;
  pfa_question: string;
  static_recommendation: string;
};

export type AssessmentFormValues = {
  slug: string;
  name: string;
  display_name: string;
  description: string;
  instructions: string;
  estimated_minutes: number | '';
  score_multiplier: number | string;
  source_reference: string;
  creator_name: string;
  creator_institution: string;
  validator_name: string;
  validator_credential: string;
  validated_at: string;
  is_active: boolean;
  sort_order: number | '';
  options: OptionRow[];
  questions: QuestionRow[];
  rules: RuleRow[];
};

export type AssessmentFormLimits = {
  max_questions: number;
  max_options: number;
  max_rules: number;
  max_option_value: number;
};

export type AssessmentFormData = {
  mode: 'create' | 'edit';
  id: number | null;
  version: string;
  has_attempts: boolean;
  attempts: string;
  values: AssessmentFormValues;
  severity_levels: string[];
  limits: AssessmentFormLimits;
};

export type FormErrors = Partial<Record<string, string>>;

export const SEVERITY_LABELS: Record<string, string> = {
  normal: 'Normal',
  mild: 'Ringan',
  moderate: 'Sedang',
  severe: 'Berat',
};

export const STATUS_TEXT: Record<string, string> = {
  'assessment-created': 'Asesmen dibuat. Kamu bisa melanjutkan mengubahnya di sini.',
  'assessment-updated': 'Perubahan disimpan.',
};

export const EMPTY_QUESTION: QuestionRow = { id: null, text: '', sub_scale: '', is_reversed: false };

export const EMPTY_RULE: RuleRow = {
  sub_scale: '',
  min_score: '',
  max_score: '',
  interpretation: '',
  severity_level: 'normal',
  trigger_pfa: false,
  pfa_question: '',
  static_recommendation: '',
};

/** Subskala unik (urutan kemunculan) dari butir yang sudah diisi. */
export function subScalesOf(questions: QuestionRow[]): string[] {
  const seen: string[] = [];
  for (const question of questions) {
    const name = question.sub_scale.trim();
    if (name !== '' && !seen.includes(name)) {
      seen.push(name);
    }
  }
  return seen;
}

/**
 * Rentang skor yang mungkin untuk satu subskala: jumlah butir x nilai terendah atau tertinggi x pengali.
 * Sama dengan yang diperiksa server (pembalikan tidak mengubah rentang). Null bila belum bisa dihitung.
 */
export function scoreRange(
  questions: QuestionRow[],
  options: OptionRow[],
  multiplier: number | string,
  subScale: string,
): { min: number; max: number } | null {
  const values = options.map((option) => option.value).filter((value): value is number => value !== '');
  const factor = Number(multiplier);
  const count = questions.filter((question) => question.sub_scale.trim() === subScale).length;

  if (values.length === 0 || count === 0 || !Number.isFinite(factor)) {
    return null;
  }

  return {
    min: Math.round(count * Math.min(...values) * factor),
    max: Math.round(count * Math.max(...values) * factor),
  };
}

/** Ubah isi kolom angka: kosong tetap kosong, selain itu bilangan. */
export function toNumberOrEmpty(raw: string): number | '' {
  if (raw.trim() === '') {
    return '';
  }
  const parsed = Number(raw);
  return Number.isFinite(parsed) ? parsed : '';
}
