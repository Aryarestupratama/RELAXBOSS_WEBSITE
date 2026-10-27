import { Link } from '@inertiajs/react';
import { ArrowRight, ClipboardCheck, Clock, ListChecks } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import InlineNote from '@/Components/shared/InlineNote';
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { ASSESSMENT_DISCLAIMER_LEAD } from '@/lib/assessment';

type ShowProps = {
  assessment: {
    slug: string;
    name: string;
    description: string;
    instructions: string | null;
    estimated_minutes: number;
    question_count: number;
    sub_scales: string[];
    options: string[];
    source: string | null;
    validated_by: string | null;
    is_demo: boolean;
  };
};

/** Pastel yang sama dengan halaman info Asesmen Publik (teks di atasnya `--text`, RULE-052). */
const TONES = ['lavender', 'sky', 'mint', 'peach'] as const;

/** Detail Asesmen sebelum mengerjakan (setara SCR-002 untuk pengguna yang sudah masuk). */
export default function Show({ assessment }: ShowProps) {
  const hasSource = assessment.source !== null || assessment.validated_by !== null;

  return (
    <AppShell title={assessment.name} wide staggered>
      <Breadcrumb data-load>
        <BreadcrumbList>
          <BreadcrumbItem>
            <BreadcrumbLink asChild>
              <Link href="/app/asesmen">Asesmen</Link>
            </BreadcrumbLink>
          </BreadcrumbItem>
          <BreadcrumbSeparator />
          <BreadcrumbItem>
            <BreadcrumbPage className="max-w-[14rem] sm:max-w-sm">{assessment.name}</BreadcrumbPage>
          </BreadcrumbItem>
        </BreadcrumbList>
      </Breadcrumb>

      <div className="mt-1 grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-4 lg:col-span-8">
          <Card data-load className="gap-4 p-5 md:p-6">
            <CardHeader className="gap-2">
              <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">{assessment.name}</h1>
              {assessment.is_demo ? (
                <Badge variant="peach" className="h-auto py-1 whitespace-normal">
                  Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.
                </Badge>
              ) : null}
              <CardDescription className="mt-1 max-w-prose leading-relaxed">{assessment.description}</CardDescription>
            </CardHeader>
            <CardContent>
              <Button asChild data-press className="group rounded-full! px-6 text-sm">
                <Link href={`/app/asesmen/${assessment.slug}/kerjakan`}>
                  Mulai asesmen
                  <ArrowRight
                    className="size-4 transition-transform duration-200 group-hover:translate-x-0.5"
                    aria-hidden="true"
                  />
                </Link>
              </Button>
            </CardContent>
          </Card>

          {assessment.sub_scales.length > 0 ? (
            <Card data-load role="region" aria-labelledby="judul-aspek" className="gap-3 p-5 md:p-6">
              <CardHeader className="gap-0.5">
                <CardTitle id="judul-aspek" role="heading" aria-level={2} className="text-base font-semibold">
                  Yang dilihat
                </CardTitle>
                <CardDescription className="text-[0.8125rem]">
                  Hasilmu ditampilkan terpisah untuk tiap aspek berikut.
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="flex flex-wrap gap-2">
                  {assessment.sub_scales.map((name, position) => (
                    <li key={name}>
                      <Badge variant={TONES[position % TONES.length]} className="px-3.5 py-1.5 text-sm font-semibold">
                        {name}
                      </Badge>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ) : null}

          <Card data-load role="region" aria-labelledby="judul-cara" className="gap-3 p-5 md:p-6">
            <CardHeader className="gap-0">
              <CardTitle id="judul-cara" role="heading" aria-level={2} className="text-base font-semibold">
                Cara mengerjakan
              </CardTitle>
            </CardHeader>
            <Separator />
            <CardContent className="space-y-2">
              {assessment.instructions ? (
                <p className="max-w-prose text-sm leading-relaxed text-text-secondary">{assessment.instructions}</p>
              ) : null}
              <p className="max-w-prose text-sm leading-relaxed text-text-secondary">
                Satu pertanyaan tampil pada satu waktu. Tidak ada jawaban benar atau salah, jawab sesuai keadaanmu.
              </p>
              {assessment.options.length > 0 ? (
                <>
                  <h3 className="pt-2 text-[0.8125rem] font-semibold">Pilihan jawaban</h3>
                  <ul className="flex flex-wrap gap-1.5">
                    {assessment.options.map((option) => (
                      <li key={option}>
                        <Badge variant="sky" className="px-3 py-1">
                          {option}
                        </Badge>
                      </li>
                    ))}
                  </ul>
                </>
              ) : null}
            </CardContent>
          </Card>
        </div>

        <div className="space-y-4 lg:col-span-4">
          <Card data-load="pop" tone="mint" role="region" aria-labelledby="judul-sekilas" className="gap-3 p-5">
            <CardHeader className="gap-3">
              <span className="grid size-10 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
                <ClipboardCheck className="size-5" />
              </span>
              <CardTitle id="judul-sekilas" role="heading" aria-level={2} className="text-base font-bold">
                Sekilas
              </CardTitle>
            </CardHeader>
            <CardContent>
              <dl className="text-sm">
                <div className="flex items-center gap-3">
                  <span
                    className="grid size-9 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary"
                    aria-hidden="true"
                  >
                    <Clock className="size-4" />
                  </span>
                  <div>
                    <dt className="text-xs text-text-secondary">Waktu pengerjaan</dt>
                    <dd className="font-semibold">Sekitar {assessment.estimated_minutes} menit</dd>
                  </div>
                </div>
                <Separator className="my-3 bg-text/10" />
                <div className="flex items-center gap-3">
                  <span
                    className="grid size-9 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary"
                    aria-hidden="true"
                  >
                    <ListChecks className="size-4" />
                  </span>
                  <div>
                    <dt className="text-xs text-text-secondary">Jumlah pertanyaan</dt>
                    <dd className="font-semibold">{assessment.question_count} pertanyaan</dd>
                  </div>
                </div>
              </dl>
            </CardContent>
          </Card>

          {hasSource ? (
            <Card data-load role="region" aria-labelledby="judul-sumber" className="gap-3 p-5">
              <CardHeader className="gap-0">
                <CardTitle id="judul-sumber" role="heading" aria-level={2} className="text-base font-semibold">
                  Tentang instrumen
                </CardTitle>
              </CardHeader>
              <Separator />
              <CardContent>
                <dl className="space-y-2 text-sm">
                  {assessment.source ? (
                    <div>
                      <dt className="text-xs text-text-secondary">Sumber</dt>
                      <dd>{assessment.source}</dd>
                    </div>
                  ) : null}
                  {assessment.validated_by ? (
                    <div>
                      <dt className="text-xs text-text-secondary">Divalidasi oleh</dt>
                      <dd>{assessment.validated_by}</dd>
                    </div>
                  ) : null}
                </dl>
              </CardContent>
            </Card>
          ) : null}

          <div data-load>
            <InlineNote>
              {ASSESSMENT_DISCLAIMER_LEAD}
              <a href="/konsultasi">bicarakan dengan profesional</a>.
            </InlineNote>
          </div>
        </div>
      </div>
    </AppShell>
  );
}
