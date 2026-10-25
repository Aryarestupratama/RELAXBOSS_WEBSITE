import { useEffect, useRef, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Info } from 'lucide-react';
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
    <AppShell title={assessment.name} wide staggered>
      <div className="mx-auto max-w-2xl">
        <div data-load>
          <div className="flex items-center justify-between gap-3">
            <Link
              href={`/app/asesmen/${assessment.slug}`}
              className="group inline-flex min-h-11 min-w-0 items-center gap-2 text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-text"
            >
              <ArrowLeft
                className="size-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5"
                aria-hidden="true"
              />
              <span className="truncate">{assessment.name}</span>
            </Link>
            <p
              className="shrink-0 rounded-full bg-card px-3 py-1 text-xs font-semibold shadow-card"
              aria-live="polite"
            >
              Butir {index + 1} dari {total}
            </p>
          </div>
          <div
            role="progressbar"
            aria-label="Kemajuan pengerjaan"
            aria-valuemin={1}
            aria-valuemax={total}
            aria-valuenow={index + 1}
            className="mt-1 grid gap-1"
            style={{ gridTemplateColumns: `repeat(${total}, minmax(0, 1fr))` }}
          >
            {questions.map((item, position) => (
              <span
                key={item.id}
                className={`h-1.5 rounded-full transition-colors duration-300 ${
                  position <= index ? 'bg-brand' : 'bg-border'
                }`}
              />
            ))}
          </div>
        </div>

        {errors.answers ? (
          <Alert variant="destructive" className="mt-4">
            <AlertDescription>{errors.answers}</AlertDescription>
          </Alert>
        ) : null}

        <section data-load className="mt-4 overflow-hidden rounded-3xl bg-card shadow-lift">
          <div className="bg-soft-lavender px-5 py-5 md:px-6">
            {index === 0 && assessment.instructions ? (
              <p className="mb-4 flex items-start gap-2 rounded-xl bg-card/70 px-3.5 py-2.5 text-[0.8125rem] leading-relaxed text-text-secondary">
                <Info className="mt-0.5 size-4 shrink-0 text-primary" aria-hidden="true" />
                {assessment.instructions}
              </p>
            ) : null}
            <h1
              key={question.id}
              ref={headingRef}
              tabIndex={-1}
              className="text-lg leading-snug font-bold tracking-tight outline-none motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 md:text-xl"
            >
              {question.text}
            </h1>
          </div>

          <div className="p-4 md:p-5">
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

            <div className="mt-4 flex items-center justify-between gap-3 border-t border-border pt-4">
              {index > 0 ? (
                <Button
                  variant="outline"
                  className="rounded-full! px-5 text-sm"
                  onClick={() => setIndex(index - 1)}
                  disabled={form.processing}
                >
                  <ArrowLeft aria-hidden="true" />
                  Sebelumnya
                </Button>
              ) : (
                <span />
              )}

              {isLast ? (
                <Button
                  className="rounded-full! px-5 text-sm"
                  onClick={submit}
                  disabled={current === null || form.processing}
                >
                  {form.processing ? 'Memproses...' : 'Lihat hasil'}
                </Button>
              ) : (
                <Button
                  className="rounded-full! px-5 text-sm"
                  onClick={() => setIndex(index + 1)}
                  disabled={current === null}
                >
                  Berikutnya
                  <ArrowRight aria-hidden="true" />
                </Button>
              )}
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
