import { useEffect, useState, type FormEvent } from 'react';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import { History, Loader2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import AiRecommendationCard from '@/Components/shared/AiRecommendationCard';
import PageHeader from '@/Components/shared/PageHeader';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import CrisisBanner from '@/Components/shared/CrisisBanner';
import DisclaimerNote from '@/Components/shared/DisclaimerNote';
import FormField from '@/Components/shared/FormField';
import ResultMeter from '@/Components/shared/ResultMeter';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
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

/** Tingkat hasil ke varian Badge (teks di atas pastel selalu `--text`, RULE-052). Teks tingkat selalu ikut tampil, bukan hanya warna. */
const SEVERITY_BADGE: Record<SeverityLevel, 'mint' | 'sky' | 'lavender' | 'peach'> = {
  normal: 'mint',
  mild: 'sky',
  moderate: 'lavender',
  severe: 'peach',
};

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
      <PageHeader
        back={{ href: '/app/riwayat', label: 'Riwayat' }}
        title={`Hasil: ${attempt.assessment_name}`}
        description={`Diselesaikan ${attempt.completed_at}`}
      />

      {attempt.crisis ? (
        <div data-load className="mb-4">
          <CrisisBanner />
        </div>
      ) : null}

      <div className="grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-4 lg:col-span-8">
          <ul className="grid gap-3 sm:grid-cols-2">
            {attempt.sub_scales.map((item) => (
              <li key={item.name} data-load>
                <Card className="h-full gap-3 p-4">
                  <CardHeader className="gap-1.5">
                    <CardDescription className="text-[0.8125rem]">{item.name}</CardDescription>
                    <h2>
                      <Badge variant={SEVERITY_BADGE[severityOf(item.severity_level)]} className="px-3 py-1 text-sm font-bold">
                        {item.interpretation}
                      </Badge>
                    </h2>
                  </CardHeader>
                  <CardContent>
                    <ResultMeter
                      subScale={item.name}
                      score={item.score}
                      maxScore={item.max_score}
                      interpretation={item.interpretation}
                      severity={item.severity_level}
                    />
                  </CardContent>
                  <p className="text-[0.8125rem] leading-relaxed text-text-secondary">
                    {SEVERITY_EXPLANATION[severityOf(item.severity_level)]}
                  </p>
                </Card>
              </li>
            ))}
          </ul>

          <div data-load>
            <DisclaimerNote>{ASSESSMENT_DISCLAIMER}</DisclaimerNote>
          </div>

          {showForm ? (
            <Card data-load role="region" aria-labelledby="pfa-title" className="gap-4 p-5">
              <CardHeader>
                <CardTitle id="pfa-title" role="heading" aria-level={2} className="text-base">
                  Mau cerita sedikit tentang konteksnya?
                </CardTitle>
                <CardDescription className="text-[0.8125rem]">
                  Boleh dijawab, boleh dilewati. Jawabanmu membantu rekomendasi terasa lebih sesuai.
                </CardDescription>
              </CardHeader>
              <Separator />

              <form onSubmit={submit} noValidate className="flex flex-col gap-4">
                {errors.answers ? (
                  <Alert variant="destructive">
                    <AlertDescription>{errors.answers}</AlertDescription>
                  </Alert>
                ) : null}

                <CardContent className="space-y-4">
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
                            className="min-h-20! rounded-xl! text-sm"
                            onChange={(event) =>
                              form.setData('answers', { ...form.data.answers, [item.name]: event.target.value })
                            }
                          />
                        )}
                      </FormField>
                    );
                  })}
                </CardContent>

                <CardFooter className="flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                  <Button type="button" variant="outline" className={pillButton} onClick={skip} disabled={form.processing}>
                    Lewati
                  </Button>
                  <Button type="submit" className={pillButton} disabled={form.processing}>
                    {form.processing ? 'Memproses...' : 'Simpan jawaban'}
                  </Button>
                </CardFooter>
              </form>
            </Card>
          ) : null}

          {savedAnswers.length > 0 ? (
            <section data-load aria-labelledby="saved-title">
              <h2 id="saved-title" className="text-base font-semibold">
                Jawabanmu sudah tersimpan
              </h2>
              <dl className="mt-2 space-y-2">
                {savedAnswers.map(([name, text]) => (
                  <Card key={name} className="gap-0.5 rounded-2xl p-4 shadow-card">
                    <dt className="text-[0.8125rem] text-text-secondary">{name}</dt>
                    <dd className="text-sm whitespace-pre-wrap">{text}</dd>
                  </Card>
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
                      <Button type="button" variant="outline" className={`mt-2 ${pillButton}`} onClick={() => void retry()}>
                        {AI_REC_RETRY}
                      </Button>
                    ) : null}
                  </AlertDescription>
                </Alert>
              ) : null}

              {recommendations.length > 0 ? (
                <ul className="mt-2 grid gap-2 sm:grid-cols-2">
                  {recommendations.map((item) => (
                    <li key={item.name}>
                      <Card className="h-full gap-0.5 rounded-2xl p-4 shadow-card">
                        <p className="text-[0.8125rem] text-text-secondary">{item.name}</p>
                        <p className="text-sm leading-relaxed">{item.recommendation}</p>
                      </Card>
                    </li>
                  ))}
                </ul>
              ) : null}
            </section>
          )}
        </div>

        <Card
          tone="mint"
          data-load
          role="complementary"
          aria-label="Langkah berikutnya"
          className="gap-3 p-5 lg:col-span-4"
        >
          <CardHeader className="gap-1">
            <CardTitle role="heading" aria-level={2} className="text-base font-bold">
              Langkah berikutnya
            </CardTitle>
            <CardDescription className="text-[0.8125rem]">
              Hasil ini gambaran awal. Bila terasa berat, kamu tidak harus menghadapinya sendirian.
            </CardDescription>
          </CardHeader>
          {/* <a> biasa: Konsultasi adalah halaman Blade */}
          <a href="/konsultasi" className={`${linkClass} inline-flex min-h-11 items-center text-[0.8125rem]`}>
            Bicara dengan profesional
          </a>
          <Separator />
          <CardFooter className="flex-col items-stretch gap-2">
            <Button asChild className={pillButton}>
              <Link href="/app/asesmen">Kembali ke asesmen</Link>
            </Button>
            <Button asChild variant="outline" className={`bg-card ${pillButton}`}>
              <Link href="/app/riwayat">
                <History aria-hidden="true" />
                Lihat riwayat
              </Link>
            </Button>
          </CardFooter>
        </Card>
      </div>

      <ConsentDialog
        open={consentOpen && needsConsent(consent)}
        onDecline={() => setConsentOpen(false)}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
