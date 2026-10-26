import { useEffect, useRef, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Info } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import LikertScale, { type LikertOption } from '@/Components/shared/LikertScale';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/Components/ui/card';
import { Progress } from '@/Components/ui/progress';
import { Separator } from '@/Components/ui/separator';

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
          <Breadcrumb>
            <BreadcrumbList>
              <BreadcrumbItem>
                <BreadcrumbLink asChild>
                  <Link href="/app/asesmen">Asesmen</Link>
                </BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbLink asChild>
                  <Link href={`/app/asesmen/${assessment.slug}`} className="max-w-[9rem] sm:max-w-xs">
                    <span className="truncate">{assessment.name}</span>
                  </Link>
                </BreadcrumbLink>
              </BreadcrumbItem>
              <BreadcrumbSeparator />
              <BreadcrumbItem>
                <BreadcrumbPage>Mengerjakan</BreadcrumbPage>
              </BreadcrumbItem>
            </BreadcrumbList>
          </Breadcrumb>

          <div className="mt-1 flex items-center justify-between gap-3">
            <Badge variant="lavender" className="px-3 py-1 text-xs font-semibold" aria-live="polite">
              Butir {index + 1} dari {total}
            </Badge>
            <p className="text-xs text-text-secondary">{answeredCount} dari {total} terjawab</p>
          </div>
          <Progress value={index + 1} max={total} aria-label="Kemajuan pengerjaan" className="mt-2" />
        </div>

        {errors.answers ? (
          <Alert variant="destructive" className="mt-4">
            <AlertDescription>{errors.answers}</AlertDescription>
          </Alert>
        ) : null}

        <Card data-load className="mt-4 gap-0 overflow-hidden">
          <CardHeader className="gap-0 bg-soft-lavender px-5 py-5 md:px-6">
            {index === 0 && assessment.instructions ? (
              <Alert role="note" className="mb-4 rounded-xl border-transparent bg-card/70 px-3.5 py-2.5">
                <Info aria-hidden="true" className="text-primary" />
                <AlertDescription className="text-[0.8125rem] leading-relaxed text-text-secondary">
                  {assessment.instructions}
                </AlertDescription>
              </Alert>
            ) : null}
            <h1
              key={question.id}
              ref={headingRef}
              tabIndex={-1}
              className="text-lg leading-snug font-bold tracking-tight outline-none motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-1 motion-safe:duration-300 md:text-xl"
            >
              {question.text}
            </h1>
          </CardHeader>

          <CardContent className="p-4 md:p-5">
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
          </CardContent>

          <Separator />

          <CardFooter className="justify-between gap-3 p-4 md:px-5">
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
          </CardFooter>
        </Card>
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
