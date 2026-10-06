import { Plus, Trash2 } from 'lucide-react';
import CheckboxField from '@/Components/shared/CheckboxField';
import FormField from '@/Components/shared/FormField';
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

type AssessmentRulesEditorProps = {
  rules: RuleRow[];
  subScales: string[];
  ranges: Record<string, { min: number; max: number } | null>;
  severityLevels: string[];
  maxRules: number;
  errors: FormErrors;
  onChange: (rules: RuleRow[]) => void;
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
  const update = (index: number, patch: Partial<RuleRow>) => {
    onChange(rules.map((rule, i) => (i === index ? { ...rule, ...patch } : rule)));
  };

  return (
    <div>
      {subScales.length === 0 ? (
        <p className="mb-3 text-sm text-text-secondary">
          Isi subskala pada butir lebih dulu. Subskala yang kamu tulis di sana muncul di sini.
        </p>
      ) : (
        <div className="mb-4 rounded-lg bg-neutral-soft px-4 py-3 text-sm">
          <p className="font-medium">Rentang skor yang harus tercakup (setelah pengali)</p>
          <ul className="mt-1 space-y-0.5">
            {subScales.map((name) => {
              const range = ranges[name];
              return (
                <li key={name}>
                  {name}: {range ? `${range.min} sampai ${range.max}` : 'belum bisa dihitung'}
                </li>
              );
            })}
          </ul>
        </div>
      )}

      {errors.rules ? (
        <p role="alert" className="mb-3 text-sm text-destructive">
          {errors.rules}
        </p>
      ) : null}

      <ol className="space-y-4">
        {rules.map((rule, index) => {
          const choices = rule.sub_scale !== '' && !subScales.includes(rule.sub_scale) ? [...subScales, rule.sub_scale] : subScales;

          return (
            <li key={index} className="rounded-lg border border-border p-4">
              <div className="flex items-center justify-between gap-2">
                <h3 className="text-base font-medium">Aturan {index + 1}</h3>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Hapus aturan ${index + 1}`}
                  disabled={rules.length <= 1}
                  onClick={() => onChange(rules.filter((_, i) => i !== index))}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              </div>

              <div className="mt-3 space-y-3">
                <div className="grid gap-3 sm:grid-cols-3">
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

                <div className="grid gap-3 sm:grid-cols-2">
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

                <CheckboxField
                  id={`rule-pfa-${index}`}
                  label="Munculkan pertanyaan konteks (PFA) untuk rentang ini"
                  checked={rule.trigger_pfa}
                  onChange={(checked) => update(index, { trigger_pfa: checked })}
                  error={errors[`rules.${index}.trigger_pfa`]}
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
        className="mt-4"
        disabled={rules.length >= maxRules}
        onClick={() => onChange([...rules, { ...EMPTY_RULE, sub_scale: subScales.length === 1 ? subScales[0] : '' }])}
      >
        <Plus aria-hidden="true" />
        Tambah aturan
      </Button>
    </div>
  );
}
