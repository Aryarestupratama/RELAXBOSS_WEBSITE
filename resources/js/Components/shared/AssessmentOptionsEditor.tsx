import { Plus, Trash2 } from 'lucide-react';
import FormField from '@/Components/shared/FormField';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { toNumberOrEmpty, type FormErrors, type OptionRow } from '@/lib/adminAssessment';

type AssessmentOptionsEditorProps = {
  options: OptionRow[];
  maxOptions: number;
  errors: FormErrors;
  onChange: (options: OptionRow[]) => void;
};

/** Pilihan jawaban satu skala untuk semua butir (Schema ENT-002 `options`). */
export default function AssessmentOptionsEditor({ options, maxOptions, errors, onChange }: AssessmentOptionsEditorProps) {
  const update = (index: number, patch: Partial<OptionRow>) => {
    onChange(options.map((option, i) => (i === index ? { ...option, ...patch } : option)));
  };

  const nextValue = options.reduce<number>((highest, option) => (option.value === '' ? highest : Math.max(highest, option.value)), -1) + 1;

  return (
    <div>
      {errors.options ? (
        <p role="alert" className="mb-3 text-sm text-destructive">
          {errors.options}
        </p>
      ) : null}

      <ul className="space-y-4">
        {options.map((option, index) => (
          <li key={index} className="grid gap-3 sm:grid-cols-[8rem_1fr_auto] sm:items-start">
            <FormField id={`option-value-${index}`} label={`Nilai pilihan ${index + 1}`} error={errors[`options.${index}.value`]}>
              {(control) => (
                <Input
                  {...control}
                  type="number"
                  inputMode="numeric"
                  min={0}
                  value={option.value}
                  onChange={(event) => update(index, { value: toNumberOrEmpty(event.target.value) })}
                />
              )}
            </FormField>

            <FormField id={`option-label-${index}`} label={`Label pilihan ${index + 1}`} error={errors[`options.${index}.label`]}>
              {(control) => (
                <Input
                  {...control}
                  maxLength={100}
                  value={option.label}
                  onChange={(event) => update(index, { label: event.target.value })}
                />
              )}
            </FormField>

            <div className="sm:pt-[1.625rem]">
              <Button
                type="button"
                variant="ghost"
                size="icon"
                aria-label={`Hapus pilihan ${index + 1}`}
                disabled={options.length <= 2}
                onClick={() => onChange(options.filter((_, i) => i !== index))}
              >
                <Trash2 aria-hidden="true" />
              </Button>
            </div>
          </li>
        ))}
      </ul>

      <Button
        type="button"
        variant="outline"
        className="mt-4"
        disabled={options.length >= maxOptions}
        onClick={() => onChange([...options, { value: nextValue, label: '' }])}
      >
        <Plus aria-hidden="true" />
        Tambah pilihan
      </Button>
    </div>
  );
}
