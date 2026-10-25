import { useEffect, useState, type FormEvent } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, History, Loader2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import AiRecommendationCard from '@/Components/shared/AiRecommendationCard';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import CrisisBanner from '@/Components/shared/CrisisBanner';
import DisclaimerNote from '@/Components/shared/DisclaimerNote';
import FormField from '@/Components/shared/FormField';
import ResultMeter from '@/Components/shared/ResultMeter';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';
import {
  AI_REC_ENABLE,
  AI_REC_LOADING,
  AI_REC_NEEDS_CONSENT,
  AI_REC_RETRY,
  AI_REC_WAITING_CONTEXT,
  ASSESSMENT_DISCLAIMER,
  SEVERITY_EXPLANATION,
  severityOf,
  type SeverityLevel,
} from '@/lib/assessment';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { useAiRecommendation } from '@/lib/useAiRecommendation';

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
    /** Jawaban PFA terindikasi krisis (dihitung ulang server setiap halaman dibuka). */
    crisis: boolean;
    ai_recommendation: string | null;
    ai_summary: string | null;
  };
  max_answer_chars: number;
};

const linkClass = 'font-medium text-brand-strong underline underline-offset-4';

/** Pastel chip tingkat hasil (teks di atasnya `--text`, RULE-052). Teks tingkat selalu ikut tampil, bukan hanya warna. */
const SEVERITY_TONE: Record<SeverityLevel, string> = {
  normal: 'bg-soft-mint',
  mild: 'bg-soft-sky',
  moderate: 'bg-soft-lavender',
  severe: 'bg-soft-peach',
};

const cardClass = 'rounded-3xl bg-card shadow-lift';
const pillButton = 'rounded-full! px-5 text-sm';

/** SCR-014. `key` memastikan state Rekomendasi AI tidak terbawa antar hasil. */
export default function Show(props: ShowProps) {
  return <ShowContent key={props.attempt.id} {...props} />;
}

function ShowContent({ attempt, max_answer_chars }: ShowProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const [consentOpen, setConsentOpen] = useState(false);

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

  // Rekomendasi AI (FR-010): hanya bila tidak terindikasi krisis, consent lengkap, dan langkah PFA sudah diputuskan.
  const consentDone = consent?.complete ?? false;
  const pfaPending = pfaItems.length > 0 && attempt.context === null;
  const saved = attempt.ai_recommendation
    ? { recommendation: attempt.ai_recommendation, summary: attempt.ai_summary }
    : null;
  const { state: ai, retry } = useAiRecommendation(
    attempt.id,
    attempt.crisis ? null : saved,
    !attempt.crisis && consentDone && !pfaPending,
  );

  return (
    <AppShell title="Hasil asesmen" wide staggered>
      <div data-load>
        <Link
          href="/app/riwayat"
          className="group inline-flex min-h-11 items-center gap-2 text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-text"
        >
          <ArrowLeft
            className="size-4 transition-transform duration-200 group-hover:-translate-x-0.5"
            aria-hidden="true"
          />
          Riwayat
        </Link>
        <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">
          Hasil: {attempt.assessment_name}
        </h1>
        <p className="mt-1 text-sm text-text-secondary">Diselesaikan {attempt.completed_at}</p>
      </div>

      {attempt.crisis ? (
        <div data-load className="mt-4">
          <CrisisBanner />
        </div>
      ) : null}

      <div className="mt-4 grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-4 lg:col-span-8">
          <ul className="grid gap-3 sm:grid-cols-2">
            {attempt.sub_scales.map((item) => (
              <li key={item.name} data-load className={`${cardClass} p-4`}>
                <p className="text-[0.8125rem] text-text-secondary">{item.name}</p>
                <h2
                  className={`mt-1 inline-block rounded-full px-3 py-1 text-sm font-bold text-text ${SEVERITY_TONE[severityOf(item.severity_level)]}`}
                >
                  {item.interpretation}
                </h2>
                <div className="mt-3">
                  <ResultMeter
                    subScale={item.name}
                    score={item.score}
                    maxScore={item.max_score}
                    interpretation={item.interpretation}
                    severity={item.severity_level}
                  />
                </div>
                <p className="mt-2 text-[0.8125rem] leading-relaxed text-text-secondary">
                  {SEVERITY_EXPLANATION[severityOf(item.severity_level)]}
                </p>
              </li>
            ))}
          </ul>

          <div data-load>
            <DisclaimerNote>{ASSESSMENT_DISCLAIMER}</DisclaimerNote>
          </div>

          {showForm ? (
            <section data-load aria-labelledby="pfa-title" className={`${cardClass} p-5`}>
              <h2 id="pfa-title" className="text-base font-semibold">
                Mau cerita sedikit tentang konteksnya?
              </h2>
              <p className="mt-1 text-[0.8125rem] text-text-secondary">
                Boleh dijawab, boleh dilewati. Jawabanmu membantu rekomendasi terasa lebih sesuai.
              </p>

              <form onSubmit={submit} noValidate className="mt-4 space-y-4">
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
                      compact
                    >
                      {(control) => (
                        <Textarea
                          {...control}
                          name={key}
                          maxLength={max_answer_chars}
                          value={value}
                          className="min-h-20! text-sm"
                          onChange={(event) =>
                            form.setData('answers', { ...form.data.answers, [item.name]: event.target.value })
                          }
                        />
                      )}
                    </FormField>
                  );
                })}

                <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                  <Button
                    type="button"
                    variant="outline"
                    className={pillButton}
                    onClick={skip}
                    disabled={form.processing}
                  >
                    Lewati
                  </Button>
                  <Button type="submit" className={pillButton} disabled={form.processing}>
                    {form.processing ? 'Memproses...' : 'Simpan jawaban'}
                  </Button>
                </div>
              </form>
            </section>
          ) : null}

          {savedAnswers.length > 0 ? (
            <section data-load aria-labelledby="saved-title">
              <h2 id="saved-title" className="text-base font-semibold">
                Jawabanmu sudah tersimpan
              </h2>
              <dl className="mt-2 space-y-2">
                {savedAnswers.map(([name, text]) => (
                  <div key={name} className="rounded-2xl bg-card p-4 shadow-card">
                    <dt className="text-[0.8125rem] text-text-secondary">{name}</dt>
                    <dd className="mt-0.5 text-sm whitespace-pre-wrap">{text}</dd>
                  </div>
                ))}
              </dl>
            </section>
          ) : null}

          {ai.status === 'ready' && !attempt.crisis ? (
            <div data-load>
              <AiRecommendationCard recommendation={ai.recommendation} summary={ai.summary} />
            </div>
          ) : (
            <section data-load aria-labelledby="rec-title">
              <h2 id="rec-title" className="text-base font-semibold">
                Rekomendasi
              </h2>

              {!attempt.crisis && !consentDone && consent !== null ? (
                <p className="mt-1 text-sm text-text-secondary">
                  {AI_REC_NEEDS_CONSENT}{' '}
                  <button type="button" onClick={() => setConsentOpen(true)} className={`${linkClass} min-h-11`}>
                    {AI_REC_ENABLE}
                  </button>
                </p>
              ) : null}

              {!attempt.crisis && consentDone && pfaPending ? (
                <p className="mt-1 text-sm text-text-secondary">{AI_REC_WAITING_CONTEXT}</p>
              ) : null}

              {!attempt.crisis && ai.status === 'loading' ? (
                <p role="status" className="mt-1 flex items-center gap-2 text-sm text-text-secondary">
                  <Loader2 className="size-4 animate-spin motion-reduce:animate-none" aria-hidden="true" />
                  {AI_REC_LOADING}
                </p>
              ) : null}

              {!attempt.crisis && ai.status === 'error' ? (
                <Alert className="mt-2">
                  <AlertDescription>
                    <p>{ai.message}</p>
                    {ai.retryable ? (
                      <Button
                        type="button"
                        variant="outline"
                        className={`mt-2 ${pillButton}`}
                        onClick={() => void retry()}
                      >
                        {AI_REC_RETRY}
                      </Button>
                    ) : null}
                  </AlertDescription>
                </Alert>
              ) : null}

              {recommendations.length > 0 ? (
                <ul className="mt-2 grid gap-2 sm:grid-cols-2">
                  {recommendations.map((item) => (
                    <li key={item.name} className="rounded-2xl bg-card p-4 shadow-card">
                      <p className="text-[0.8125rem] text-text-secondary">{item.name}</p>
                      <p className="mt-0.5 text-sm leading-relaxed">{item.recommendation}</p>
                    </li>
                  ))}
                </ul>
              ) : null}
            </section>
          )}
        </div>

        <aside data-load aria-label="Langkah berikutnya" className="rounded-3xl bg-soft-mint p-5 text-text shadow-lift lg:col-span-4">
          <h2 className="text-base font-bold">Langkah berikutnya</h2>
          <p className="mt-1 text-[0.8125rem] text-text-secondary">
            Hasil ini gambaran awal. Bila terasa berat, kamu tidak harus menghadapinya sendirian.
          </p>
          {/* <a> biasa: Konsultasi adalah halaman Blade */}
          <a href="/konsultasi" className={`${linkClass} mt-1 inline-flex min-h-11 items-center text-[0.8125rem]`}>
            Bicara dengan profesional
          </a>
          <div className="mt-2 flex flex-col gap-2">
            <Button asChild className={pillButton}>
              <Link href="/app/asesmen">Kembali ke asesmen</Link>
            </Button>
            <Button asChild variant="outline" className={`bg-card ${pillButton}`}>
              <Link href="/app/riwayat">
                <History aria-hidden="true" />
                Lihat riwayat
              </Link>
            </Button>
          </div>
        </aside>
      </div>

      <ConsentDialog
        open={consentOpen && needsConsent(consent)}
        onDecline={() => setConsentOpen(false)}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
