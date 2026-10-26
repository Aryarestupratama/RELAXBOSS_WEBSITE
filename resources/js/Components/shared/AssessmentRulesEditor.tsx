import { useState } from 'react';
import { CircleAlert, Plus, Trash2 } from 'lucide-react';
import ConfirmRemoveDialog from '@/Components/shared/ConfirmRemoveDialog';
import FormField from '@/Components/shared/FormField';
import SwitchField from '@/Components/shared/SwitchField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { Textarea } from '@/Components/ui/textarea';
import {
  EMPTY_RULE,
  SEVERITY_LABELS,
  toNumberOrEmpty,
  type FormErrors,
  type RuleRow,
} from '@/lib/adminAssessment';
import { cn } from '@/lib/utils';

type AssessmentRulesEditorProps = {
  rules: RuleRow[];
  subScales: string[];
  ranges: Record<string, { min: number; max: number } | null>;
  severityLevels: string[];
  maxRules: number;
  errors: FormErrors;
  onChange: (rules: RuleRow[]) => void;
};

const SEVERITY_BADGE: Record<string, string> = {
  normal: 'bg-soft-mint',
  mild: 'bg-soft-sky',
  moderate: 'bg-soft-peach',
  severe: 'bg-crisis-bg text-crisis-text!',
};

/** Aturan skor per subskala: rentang inklusif, interpretasi, keparahan, PFA, dan rekomendasi statis. */
export default function AssessmentRulesEditor({
  rules,
  subScales,
  ranges,
  severityLevels,
  maxRules,
  errors,
  onChange,
}: AssessmentRulesEditorProps) {
  const [pending, setPending] = useState<number | null>(null);

  const update = (index: number, patch: Partial<RuleRow>) => {
    onChange(rules.map((rule, i) => (i === index ? { ...rule, ...patch } : rule)));
  };

  const removeAt = (index: number) => onChange(rules.filter((_, i) => i !== index));

  /** Aturan kosong langsung dihapus; yang sudah berisi minta konfirmasi dulu. */
  const requestRemove = (index: number) => {
    const rule = rules[index];
    const empty =
      rule.interpretation.trim() === '' &&
      rule.static_recommendation.trim() === '' &&
      rule.pfa_question.trim() === '' &&
      rule.min_score === '' &&
      rule.max_score === '';
    if (empty) {
      removeAt(index);
    } else {
      setPending(index);
    }
  };

  return (
    <div>
      {subScales.length === 0 ? (
        <p className="mb-4 rounded-2xl border border-border/60 bg-card px-4 py-3 text-sm text-text-secondary">
          Isi subskala pada butir lebih dulu. Subskala yang kamu tulis di sana muncul di sini.
        </p>
      ) : (
        <div className="mb-4 rounded-2xl bg-soft-sky p-4 text-sm text-text">
          <p className="font-semibold">Rentang skor yang harus tercakup (setelah pengali)</p>
          <ul className="mt-2 flex flex-wrap gap-2">
            {subScales.map((name) => {
              const range = ranges[name];
              return (
                <li key={name}>
                  <Badge variant="outline" className="px-3 py-1 text-[0.8125rem]">
                    {name}: {range ? `${range.min} sampai ${range.max}` : 'belum bisa dihitung'}
                  </Badge>
                </li>
              );
            })}
          </ul>
        </div>
      )}

      {errors.rules ? (
        <Alert variant="destructive" className="mb-4 rounded-2xl">
          <CircleAlert aria-hidden="true" />
          <AlertDescription>{errors.rules}</AlertDescription>
        </Alert>
      ) : null}

      <ol className="space-y-4">
        {rules.map((rule, index) => {
          const choices = rule.sub_scale !== '' && !subScales.includes(rule.sub_scale) ? [...subScales, rule.sub_scale] : subScales;

          return (
            <li key={index} className="rounded-2xl border border-border bg-card p-4 md:p-5">
              <div className="flex items-center gap-3">
                <span
                  className="grid size-8 shrink-0 place-items-center rounded-full bg-soft-peach text-sm font-bold text-text tabular-nums"
                  aria-hidden="true"
                >
                  {index + 1}
                </span>
                <h3 className="min-w-0 flex-1 text-base font-semibold">Aturan {index + 1}</h3>
                {rule.min_score !== '' && rule.max_score !== '' ? (
                  <Badge variant="outline" className="tabular-nums">
                    {rule.min_score} sampai {rule.max_score}
                  </Badge>
                ) : null}
                <Badge variant="secondary" className={cn(SEVERITY_BADGE[rule.severity_level])}>
                  {SEVERITY_LABELS[rule.severity_level] ?? rule.severity_level}
                </Badge>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className="-mr-2 rounded-full! hover:bg-card!"
                  aria-label={`Hapus aturan ${index + 1}`}
                  disabled={rules.length <= 1}
                  onClick={() => requestRemove(index)}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              </div>

              <div className="mt-4 space-y-4">
                <div className="grid gap-4 sm:grid-cols-3">
                  <FormField id={`rule-subscale-${index}`} label="Subskala" error={errors[`rules.${index}.sub_scale`]}>
                    {(control) => (
                      <NativeSelect
                        {...control}
                        value={rule.sub_scale}
                        onChange={(event) => update(index, { sub_scale: event.target.value })}
                      >
                        <option value="">Pilih subskala</option>
                        {choices.map((name) => (
                          <option key={name} value={name}>
                            {name}
                          </option>
                        ))}
                      </NativeSelect>
                    )}
                  </FormField>

                  <FormField id={`rule-min-${index}`} label="Skor minimal" error={errors[`rules.${index}.min_score`]}>
                    {(control) => (
                      <Input
                        {...control}
                        type="number"
                        inputMode="numeric"
                        min={0}
                        value={rule.min_score}
                        onChange={(event) => update(index, { min_score: toNumberOrEmpty(event.target.value) })}
                      />
                    )}
                  </FormField>

                  <FormField id={`rule-max-${index}`} label="Skor maksimal" error={errors[`rules.${index}.max_score`]}>
                    {(control) => (
                      <Input
                        {...control}
                        type="number"
                        inputMode="numeric"
                        min={0}
                        value={rule.max_score}
                        onChange={(event) => update(index, { max_score: toNumberOrEmpty(event.target.value) })}
                      />
                    )}
                  </FormField>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                  <FormField id={`rule-interpretation-${index}`} label="Interpretasi" error={errors[`rules.${index}.interpretation`]}>
                    {(control) => (
                      <Input
                        {...control}
                        maxLength={100}
                        value={rule.interpretation}
                        onChange={(event) => update(index, { interpretation: event.target.value })}
                      />
                    )}
                  </FormField>

                  <FormField id={`rule-severity-${index}`} label="Tingkat keparahan" error={errors[`rules.${index}.severity_level`]}>
                    {(control) => (
                      <NativeSelect
                        {...control}
                        value={rule.severity_level}
                        onChange={(event) => update(index, { severity_level: event.target.value })}
                      >
                        {severityLevels.map((level) => (
                          <option key={level} value={level}>
                            {SEVERITY_LABELS[level] ?? level}
                          </option>
                        ))}
                      </NativeSelect>
                    )}
                  </FormField>
                </div>

                <SwitchField
                  id={`rule-pfa-${index}`}
                  label="Munculkan pertanyaan konteks (PFA) untuk rentang ini"
                  checked={rule.trigger_pfa}
                  onChange={(checked) => update(index, { trigger_pfa: checked })}
                  error={errors[`rules.${index}.trigger_pfa`]}
                  className="[&>div]:bg-card"
                />

                {rule.trigger_pfa ? (
                  <FormField id={`rule-pfa-question-${index}`} label="Pertanyaan PFA" error={errors[`rules.${index}.pfa_question`]}>
                    {(control) => (
                      <Textarea
                        {...control}
                        className="min-h-20"
                        maxLength={500}
                        value={rule.pfa_question}
                        onChange={(event) => update(index, { pfa_question: event.target.value })}
                      />
                    )}
                  </FormField>
                ) : null}

                <FormField
                  id={`rule-recommendation-${index}`}
                  label="Rekomendasi statis"
                  hint="Dipakai bila Rekomendasi AI tidak tersedia atau pengguna belum memberi consent."
                  error={errors[`rules.${index}.static_recommendation`]}
                >
                  {(control) => (
                    <Textarea
                      {...control}
                      maxLength={2000}
                      value={rule.static_recommendation}
                      onChange={(event) => update(index, { static_recommendation: event.target.value })}
                    />
                  )}
                </FormField>
              </div>
            </li>
          );
        })}
      </ol>

      <Button
        type="button"
        variant="outline"
        className="mt-4 w-full rounded-2xl! border-dashed"
        disabled={rules.length >= maxRules}
        onClick={() => onChange([...rules, { ...EMPTY_RULE, sub_scale: subScales.length === 1 ? subScales[0] : '' }])}
      >
        <Plus aria-hidden="true" />
        Tambah aturan
      </Button>

      <ConfirmRemoveDialog
        open={pending !== null}
        title={`Hapus aturan ${pending !== null ? pending + 1 : ''}?`}
        description="Aturan ini dan isinya dihapus dari formulir. Perubahan baru tersimpan setelah kamu menekan simpan."
        confirmLabel="Hapus aturan"
        onCancel={() => setPending(null)}
        onConfirm={() => {
          if (pending !== null) {
            removeAt(pending);
          }
          setPending(null);
        }}
      />
    </div>
  );
}
