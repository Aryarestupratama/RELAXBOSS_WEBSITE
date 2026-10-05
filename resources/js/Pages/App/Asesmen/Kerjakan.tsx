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
    <AppShell title={assessment.name}>
      <div className="mx-auto max-w-2xl">
        <p className="text-sm text-text-secondary">{assessment.name}</p>

        <div
          role="progressbar"
          aria-label="Kemajuan pengerjaan"
          aria-valuemin={1}
          aria-valuemax={total}
          aria-valuenow={index + 1}
          className="mt-3 h-2 overflow-hidden rounded-full bg-neutral-soft"
        >
          <div
            className="h-full rounded-full bg-brand-strong transition-[width] duration-200"
            style={{ width: `${((index + 1) / total) * 100}%` }}
          />
        </div>
        <p className="mt-2 text-sm text-text-secondary" aria-live="polite">
          Butir {index + 1} dari {total}
        </p>

        {index === 0 && assessment.instructions ? (
          <p className="mt-4 rounded-lg border-l-4 border-brand-strong bg-neutral-soft px-4 py-3 text-text-secondary">
            {assessment.instructions}
          </p>
        ) : null}

        {errors.answers ? (
          <Alert variant="destructive" className="mt-4">
            <AlertDescription>{errors.answers}</AlertDescription>
          </Alert>
        ) : null}

        <section className="mt-6 rounded-xl border border-border bg-card p-5 shadow-card md:p-6">
          <h1 ref={headingRef} tabIndex={-1} className="text-xl font-semibold outline-none">
            {question.text}
          </h1>

          <div className="mt-5">
            <LikertScale
              key={question.id}
              name={`butir-${question.id}`}
              legend={question.text}
              options={options}
              value={current}
              onChange={choose}
            />
          </div>
        </section>

        <div className="mt-6 flex items-center justify-between gap-3">
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
