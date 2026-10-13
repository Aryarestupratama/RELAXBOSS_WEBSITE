import { useEffect, useRef, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import LikertScale, { type LikertOption } from '@/Components/shared/LikertScale';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';

type Question = {
  id: number;
  text: string;
};

type KerjakanProps = {
  assessment: {
    slug: string;
    name: string;
    instructions: string | null;
    options: LikertOption[];
    questions: Question[];
  };
};

type PendingVisit = {
  url: string;
  method: 'get' | 'post' | 'put' | 'patch' | 'delete';
};

/** SCR-013. Jawaban hanya di state lokal sampai "Lihat hasil"; skor dihitung di server (FR-007). */
export default function Kerjakan({ assessment }: KerjakanProps) {
  const { questions, options } = assessment;
  const total = questions.length;

  const form = useForm<{ answers: Record<number, number> }>({ answers: {} });
  const errors = form.errors as Partial<Record<string, string>>;

  const [index, setIndex] = useState(0);
  const [pending, setPending] = useState<PendingVisit | null>(null);
  const headingRef = useRef<HTMLHeadingElement>(null);
  const allowLeave = useRef(false);
  const mounted = useRef(false);

  const question = questions[index];
  const answeredCount = Object.keys(form.data.answers).length;
  const current = form.data.answers[question.id] ?? null;
  const isLast = index === total - 1;
  const hasProgress = answeredCount > 0 && !allowLeave.current;

  // Pindah butir: fokus ke teks butir agar keyboard dan pembaca layar mengikuti.
  useEffect(() => {
    if (mounted.current) {
      headingRef.current?.focus();
    } else {
      mounted.current = true;
    }
  }, [index]);

  // Peringatan sebelum keluar lewat navigasi di dalam aplikasi.
  useEffect(() => {
    return router.on('before', (event) => {
      if (allowLeave.current || answeredCount === 0) {
        return;
      }
      const visit = event.detail.visit;
      setPending({ url: visit.url.href, method: visit.method });
      return false;
    });
  }, [answeredCount]);

  // Peringatan bawaan browser saat tab ditutup atau dimuat ulang.
  useEffect(() => {
    if (!hasProgress) {
      return;
    }
    const handler = (event: BeforeUnloadEvent) => {
      event.preventDefault();
    };
    window.addEventListener('beforeunload', handler);
    return () => window.removeEventListener('beforeunload', handler);
  }, [hasProgress]);

  const choose = (value: number) => {
    form.setData('answers', { ...form.data.answers, [question.id]: value });
  };

  const submit = () => {
    allowLeave.current = true;
    form.post(`/app/asesmen/${assessment.slug}/hasil`, {
      onError: () => {
        allowLeave.current = false;
      },
    });
  };

  const leave = () => {
    if (!pending) {
      return;
    }
    allowLeave.current = true;
    const target = pending;
    setPending(null);
    router.visit(target.url, { method: target.method });
  };

  return (
    <AppShell title={assessment.name} staggered>
      <div className="mx-auto max-w-4xl">
        <div data-load className="text-center">
          <p className="text-sm font-medium text-text-secondary">{assessment.name}</p>
          <div
            role="progressbar"
            aria-label="Kemajuan pengerjaan"
            aria-valuemin={1}
            aria-valuemax={total}
            aria-valuenow={index + 1}
            className="mt-3 grid gap-1"
            style={{ gridTemplateColumns: `repeat(${total}, minmax(0, 1fr))` }}
          >
            {questions.map((item, position) => (
              <span
                key={item.id}
                className={`h-1.5 rounded-full transition-colors duration-300 ${
                  position <= index ? 'bg-primary' : 'bg-border'
                }`}
              />
            ))}
          </div>
          <p className="mt-2 text-sm text-text-secondary" aria-live="polite">
            Butir {index + 1} dari {total}
          </p>
        </div>

        {errors.answers ? (
          <Alert variant="destructive" className="mt-4">
            <AlertDescription>{errors.answers}</AlertDescription>
          </Alert>
        ) : null}

        <section data-load className="mt-8 overflow-hidden rounded-3xl border border-border bg-card shadow-card">
          <div className="grid lg:grid-cols-12">
            <div className="flex flex-col gap-5 bg-brand-soft/40 p-6 md:p-8 lg:col-span-5">
              <img
                src="/images/asesmen/asesmen-kerjakan.webp"
                width={160}
                height={160}
                alt=""
                data-float
                className="size-24 object-contain lg:size-40"
              />

              {index === 0 && assessment.instructions ? (
                <p className="rounded-lg border border-border border-l-4 border-l-brand-strong bg-card px-4 py-3 text-sm text-text-secondary">
                  {assessment.instructions}
                </p>
              ) : null}

              <h1
                key={question.id}
                ref={headingRef}
                tabIndex={-1}
                className="text-2xl leading-snug font-semibold outline-none motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 md:text-[1.75rem]"
              >
                {question.text}
              </h1>
            </div>

            <div className="flex flex-col justify-between gap-8 p-6 md:p-8 lg:col-span-7">
              <div
                key={question.id}
                className="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-right-2 motion-safe:duration-300"
              >
                <LikertScale
                  name={`butir-${question.id}`}
                  legend={question.text}
                  options={options}
                  value={current}
                  onChange={choose}
                  disabled={form.processing}
                />
              </div>

              <div className="flex items-center justify-between gap-3">
                {index > 0 ? (
                  <Button variant="outline" onClick={() => setIndex(index - 1)} disabled={form.processing}>
                    <ArrowLeft aria-hidden="true" />
                    Sebelumnya
                  </Button>
                ) : (
                  <span />
                )}

                {isLast ? (
                  <Button onClick={submit} disabled={current === null || form.processing}>
                    {form.processing ? 'Memproses...' : 'Lihat hasil'}
                  </Button>
                ) : (
                  <Button onClick={() => setIndex(index + 1)} disabled={current === null}>
                    Berikutnya
                    <ArrowRight aria-hidden="true" />
                  </Button>
                )}
              </div>
            </div>
          </div>
        </section>
      </div>

      <ConfirmLeaveDialog
        open={pending !== null}
        title="Keluar dari asesmen?"
        description="Jawabanmu belum tersimpan. Yakin ingin keluar?"
        stayLabel="Lanjut mengerjakan"
        leaveLabel="Keluar"
        onStay={() => setPending(null)}
        onLeave={leave}
      />
    </AppShell>
  );
}
