import { useState } from 'react';
import { ArrowDown, ArrowUp, CircleAlert, Plus, Trash2 } from 'lucide-react';
import ConfirmRemoveDialog from '@/Components/shared/ConfirmRemoveDialog';
import FormField from '@/Components/shared/FormField';
import SwitchField from '@/Components/shared/SwitchField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
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

const ICON_BUTTON = 'rounded-full! hover:bg-card!';

/** Butir pertanyaan: teks, subskala, dan penanda terbalik. Urutan di daftar = urutan tampil. */
export default function AssessmentQuestionsEditor({
  questions,
  subScales,
  maxQuestions,
  errors,
  onChange,
}: AssessmentQuestionsEditorProps) {
  const [pending, setPending] = useState<number | null>(null);

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

  const removeAt = (index: number) => onChange(questions.filter((_, i) => i !== index));

  /** Butir kosong langsung dihapus; yang sudah berisi minta konfirmasi dulu. */
  const requestRemove = (index: number) => {
    const question = questions[index];
    if (question.text.trim() === '' && question.sub_scale.trim() === '') {
      removeAt(index);
    } else {
      setPending(index);
    }
  };

  return (
    <div>
      <datalist id="subscale-suggestions">
        {subScales.map((name) => (
          <option key={name} value={name} />
        ))}
      </datalist>

      {errors.questions ? (
        <Alert variant="destructive" className="mb-4 rounded-2xl">
          <CircleAlert aria-hidden="true" />
          <AlertDescription>{errors.questions}</AlertDescription>
        </Alert>
      ) : null}

      <ol className="space-y-4">
        {questions.map((question, index) => (
          <li key={index} className="rounded-2xl border border-border bg-neutral-soft/50 p-4 md:p-5">
            <div className="flex items-center gap-3">
              <span
                className="grid size-8 shrink-0 place-items-center rounded-full bg-soft-lavender text-sm font-bold text-text tabular-nums"
                aria-hidden="true"
              >
                {index + 1}
              </span>
              <h3 className="min-w-0 flex-1 text-base font-semibold">Butir {index + 1}</h3>
              {question.sub_scale.trim() !== '' ? (
                <Badge variant="sky" className="hidden max-w-40 truncate sm:inline-flex">
                  {question.sub_scale.trim()}
                </Badge>
              ) : null}
              {question.is_reversed ? <Badge variant="peach">Terbalik</Badge> : null}
              <div className="-mr-2 flex">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className={ICON_BUTTON}
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
                  className={ICON_BUTTON}
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
                  className={ICON_BUTTON}
                  aria-label={`Hapus butir ${index + 1}`}
                  disabled={questions.length <= 1}
                  onClick={() => requestRemove(index)}
                >
                  <Trash2 aria-hidden="true" />
                </Button>
              </div>
            </div>

            <div className="mt-4 space-y-4">
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

              <SwitchField
                id={`question-reversed-${index}`}
                label="Butir terbalik (skornya dibalik saat dihitung)"
                checked={question.is_reversed}
                onChange={(checked) => update(index, { is_reversed: checked })}
                error={errors[`questions.${index}.is_reversed`] ?? errors[`questions.${index}.id`]}
                className="[&>div]:bg-card"
              />
            </div>
          </li>
        ))}
      </ol>

      <Button
        type="button"
        variant="outline"
        className="mt-4 w-full rounded-2xl! border-dashed"
        disabled={questions.length >= maxQuestions}
        onClick={() => onChange([...questions, { ...EMPTY_QUESTION }])}
      >
        <Plus aria-hidden="true" />
        Tambah butir
      </Button>

      <ConfirmRemoveDialog
        open={pending !== null}
        title={`Hapus butir ${pending !== null ? pending + 1 : ''}?`}
        description="Butir ini dan isinya dihapus dari formulir. Perubahan baru tersimpan setelah kamu menekan simpan."
        confirmLabel="Hapus butir"
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
