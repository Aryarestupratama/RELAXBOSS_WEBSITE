import { ArrowDown, ArrowUp, Plus, Trash2 } from 'lucide-react';
import CheckboxField from '@/Components/shared/CheckboxField';
import FormField from '@/Components/shared/FormField';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import { EMPTY_QUESTION, type FormErrors, type QuestionRow } from '@/lib/adminAssessment';

type AssessmentQuestionsEditorProps = {
  questions: QuestionRow[];
  subScales: string[];
  maxQuestions: number;
  errors: FormErrors;
  onChange: (questions: QuestionRow[]) => void;
};

/** Butir pertanyaan: teks, subskala, dan penanda terbalik. Urutan di daftar = urutan tampil. */
export default function AssessmentQuestionsEditor({
  questions,
  subScales,
  maxQuestions,
  errors,
  onChange,
}: AssessmentQuestionsEditorProps) {
  const update = (index: number, patch: Partial<QuestionRow>) => {
    onChange(questions.map((question, i) => (i === index ? { ...question, ...patch } : question)));
  };

  const move = (index: number, direction: -1 | 1) => {
    const target = index + direction;
    if (target < 0 || target >= questions.length) {
      return;
    }
    const next = [...questions];
    [next[index], next[target]] = [next[target], next[index]];
    onChange(next);
  };

  return (
    <div>
      <datalist id="subscale-suggestions">
        {subScales.map((name) => (
          <option key={name} value={name} />
        ))}
      </datalist>

      {errors.questions ? (
        <p role="alert" className="mb-3 text-sm text-destructive">
          {errors.questions}
        </p>
      ) : null}

      <ol className="space-y-4">
        {questions.map((question, index) => (
          <li key={index} className="rounded-lg border border-border p-4">
            <div className="flex items-center justify-between gap-2">
              <h3 className="text-base font-medium">Butir {index + 1}</h3>
              <div className="flex">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Naikkan butir ${index + 1}`}
                  disabled={index === 0}
                  onClick={() => move(index, -1)}
                >
                  <ArrowUp aria-hidden="true" />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Turunkan butir ${index + 1}`}
                  disabled={index === questions.length - 1}
                  onClick={() => move(index, 1)}
                >
                  <ArrowDown aria-hidden="true" />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  aria-label={`Hapus butir ${index + 1}`}
                  disabled={questions.length <= 1}
                  onClick={() => onChange(questions.filter((_, i) => i !== index))}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              </div>
            </div>

            <div className="mt-3 space-y-3">
              <FormField id={`question-text-${index}`} label="Teks butir" error={errors[`questions.${index}.text`]}>
                {(control) => (
                  <Textarea
                    {...control}
                    className="min-h-20"
                    maxLength={500}
                    value={question.text}
                    onChange={(event) => update(index, { text: event.target.value })}
                  />
                )}
              </FormField>

              <FormField id={`question-subscale-${index}`} label="Subskala" error={errors[`questions.${index}.sub_scale`]}>
                {(control) => (
                  <Input
                    {...control}
                    list="subscale-suggestions"
                    maxLength={50}
                    value={question.sub_scale}
                    onChange={(event) => update(index, { sub_scale: event.target.value })}
                  />
                )}
              </FormField>

              <CheckboxField
                id={`question-reversed-${index}`}
                label="Butir terbalik (skornya dibalik saat dihitung)"
                checked={question.is_reversed}
                onChange={(checked) => update(index, { is_reversed: checked })}
                error={errors[`questions.${index}.is_reversed`] ?? errors[`questions.${index}.id`]}
              />
            </div>
          </li>
        ))}
      </ol>

      <Button
        type="button"
        variant="outline"
        className="mt-4"
        disabled={questions.length >= maxQuestions}
        onClick={() => onChange([...questions, { ...EMPTY_QUESTION }])}
      >
        <Plus aria-hidden="true" />
        Tambah butir
      </Button>
    </div>
  );
}
