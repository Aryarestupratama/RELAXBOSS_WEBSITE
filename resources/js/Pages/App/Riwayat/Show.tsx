import { useEffect, type FormEvent } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { History } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import DisclaimerNote from '@/Components/shared/DisclaimerNote';
import FormField from '@/Components/shared/FormField';
import ResultMeter from '@/Components/shared/ResultMeter';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import { ASSESSMENT_DISCLAIMER, SEVERITY_EXPLANATION, severityOf } from '@/lib/assessment';

type SubScale = {
  name: string;
  score: number;
  max_score: number;
  interpretation: string;
  severity_level: string;
  trigger_pfa: boolean;
  pfa_question: string | null;
  recommendation: string | null;
};

type ShowProps = {
  attempt: {
    id: number;
    assessment_name: string;
    completed_at: string;
    sub_scales: SubScale[];
    /** null = belum memutuskan; objek (boleh kosong) = sudah menjawab atau melewati. */
    context: Record<string, string> | null;
  };
  max_answer_chars: number;
};

const linkClass = 'font-medium text-brand-strong underline underline-offset-4';

/** SCR-014 */
export default function Show({ attempt, max_answer_chars }: ShowProps) {
  const pfaItems = attempt.sub_scales.filter((item) => item.trigger_pfa && item.pfa_question);
  const answerUrl = `/app/riwayat/${attempt.id}/konteks`;

  const form = useForm<{ answers: Record<string, string> }>({
    answers: Object.fromEntries(pfaItems.map((item) => [item.name, ''])),
  });
  const errors = form.errors as Partial<Record<string, string>>;
  const hasErrors = Object.keys(form.errors).length > 0;

  useEffect(() => {
    if (hasErrors) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [hasErrors]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post(answerUrl, { preserveScroll: true });
  };

  const skip = () => {
    router.post(
      answerUrl,
      { answers: Object.fromEntries(pfaItems.map((item) => [item.name, ''])) },
      { preserveScroll: true },
    );
  };

  const savedAnswers = attempt.context ? Object.entries(attempt.context) : [];
  const showForm = pfaItems.length > 0 && attempt.context === null;
  const recommendations = attempt.sub_scales.filter((item) => item.recommendation);

  return (
    <AppShell title="Hasil asesmen">
      <div className="mx-auto max-w-3xl">
        <h1>Hasil: {attempt.assessment_name}</h1>
        <p className="mt-1 text-sm text-text-secondary">Diselesaikan {attempt.completed_at}</p>

        <ul className="mt-6 grid gap-4">
          {attempt.sub_scales.map((item) => (
            <li key={item.name} className="rounded-xl border border-border bg-card p-5 shadow-card">
              <p className="text-sm text-text-secondary">{item.name}</p>
              <h2 className="mt-0.5 text-xl font-semibold">{item.interpretation}</h2>
              <div className="mt-3">
                <ResultMeter
                  subScale={item.name}
                  score={item.score}
                  maxScore={item.max_score}
                  interpretation={item.interpretation}
                  severity={item.severity_level}
                />
              </div>
              <p className="mt-3 text-text-secondary">{SEVERITY_EXPLANATION[severityOf(item.severity_level)]}</p>
            </li>
          ))}
        </ul>

        {showForm ? (
          <section aria-labelledby="pfa-title" className="mt-8 rounded-xl border border-border bg-card p-5 shadow-card">
            <h2 id="pfa-title" className="text-lg font-semibold">
              Mau cerita sedikit tentang konteksnya?
            </h2>
            <p className="mt-1 text-text-secondary">
              Boleh dijawab, boleh dilewati. Jawabanmu membantu rekomendasi terasa lebih sesuai.
            </p>

            <form onSubmit={submit} noValidate className="mt-5 space-y-5">
              {errors.answers ? (
                <Alert variant="destructive">
                  <AlertDescription>{errors.answers}</AlertDescription>
                </Alert>
              ) : null}

              {pfaItems.map((item, index) => {
                const key = `answers.${item.name}`;
                const value = form.data.answers[item.name] ?? '';
                return (
                  <FormField
                    key={item.name}
                    id={`pfa-${index}`}
                    label={`${item.name}: ${item.pfa_question}`}
                    error={errors[key]}
                    hint={`${value.length}/${max_answer_chars} karakter`}
                  >
                    {(control) => (
                      <Textarea
                        {...control}
                        name={key}
                        maxLength={max_answer_chars}
                        value={value}
                        onChange={(event) =>
                          form.setData('answers', { ...form.data.answers, [item.name]: event.target.value })
                        }
                      />
                    )}
                  </FormField>
                );
              })}

              <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Button type="button" variant="outline" onClick={skip} disabled={form.processing}>
                  Lewati
                </Button>
                <Button type="submit" disabled={form.processing}>
                  {form.processing ? 'Memproses...' : 'Simpan jawaban'}
                </Button>
              </div>
            </form>
          </section>
        ) : null}

        {savedAnswers.length > 0 ? (
          <section aria-labelledby="saved-title" className="mt-8">
            <h2 id="saved-title" className="text-lg font-semibold">
              Jawabanmu sudah tersimpan
            </h2>
            <dl className="mt-3 space-y-3">
              {savedAnswers.map(([name, text]) => (
                <div key={name} className="rounded-lg border border-border bg-card p-4">
                  <dt className="text-sm text-text-secondary">{name}</dt>
                  <dd className="mt-1 whitespace-pre-wrap">{text}</dd>
                </div>
              ))}
            </dl>
          </section>
        ) : null}

        <div className="mt-8">
          <DisclaimerNote>{ASSESSMENT_DISCLAIMER}</DisclaimerNote>
        </div>

        {recommendations.length > 0 ? (
          <section aria-labelledby="rec-title" className="mt-8">
            <h2 id="rec-title" className="text-lg font-semibold">
              Rekomendasi
            </h2>
            <ul className="mt-3 space-y-3">
              {recommendations.map((item) => (
                <li key={item.name} className="rounded-lg border border-border bg-card p-4">
                  <p className="text-sm text-text-secondary">{item.name}</p>
                  <p className="mt-1">{item.recommendation}</p>
                </li>
              ))}
            </ul>
          </section>
        ) : null}

        <p className="mt-6">
          {/* <a> biasa: Konsultasi adalah halaman Blade */}
          <a href="/konsultasi" className={linkClass}>
            Bicara dengan profesional
          </a>
        </p>

        <div className="mt-6 flex flex-wrap gap-3">
          <Button asChild variant="outline">
            <Link href="/app/riwayat">
              <History aria-hidden="true" />
              Lihat riwayat
            </Link>
          </Button>
          <Button asChild>
            <Link href="/app/asesmen">Kembali ke asesmen</Link>
          </Button>
        </div>
      </div>
    </AppShell>
  );
}
