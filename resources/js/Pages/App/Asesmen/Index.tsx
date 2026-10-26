import { Link } from '@inertiajs/react';
import { ArrowUpRight, Clock, History, ListChecks } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';

type AssessmentCard = {
  slug: string;
  name: string;
  description: string;
  estimated_minutes: number;
  question_count: number;
  is_validated: boolean;
};

type IndexProps = {
  assessments: AssessmentCard[];
};

/** Pastel bergantian, sama dengan kartu fitur Beranda dan Dashboard (teks di atasnya `--text`, RULE-052). */
const TONES = ['peach', 'mint', 'lavender', 'sky'] as const;

/** SCR-012 */
export default function Index({ assessments }: IndexProps) {
  return (
    <AppShell title="Asesmen" wide staggered>
      <PageHeader
        title="Asesmen"
        description="Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis."
        actions={
          <Button asChild variant="outline" data-press className="rounded-full! px-4 text-[0.8125rem]">
            <Link href="/app/riwayat">
              <History className="size-4" aria-hidden="true" />
              Lihat riwayat
            </Link>
          </Button>
        }
      />

      <div>
        {assessments.length === 0 ? (
          <Card data-load="pop" className="mx-auto max-w-md items-center gap-3 p-6 text-center">
            <img
              src="/images/asesmen/asesmen-kosong.webp"
              width={160}
              height={160}
              alt=""
              className="size-28 object-contain"
            />
            <p className="max-w-sm text-sm text-text-secondary">
              Belum ada asesmen yang tersedia. Coba lagi nanti, ya.
            </p>
            <Button asChild variant="outline" data-press className="rounded-full! px-5 text-sm">
              <Link href="/dashboard">Ke Dashboard</Link>
            </Button>
          </Card>
        ) : (
          <ul className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            {assessments.map((assessment, index) => (
              <li key={assessment.slug} data-load>
                <Link
                  href={`/app/asesmen/${assessment.slug}`}
                  data-lift
                  data-press
                  className="group relative block h-full rounded-3xl transition-shadow duration-300 hover:shadow-lift-hover"
                >
                  <Card
                    tone={TONES[index % TONES.length]}
                    className="h-full min-h-44 gap-3 p-5 pb-16 transition-shadow duration-300 group-hover:shadow-lift-hover"
                  >
                    <CardHeader className="gap-1">
                      <CardTitle className="text-base font-bold">{assessment.name}</CardTitle>
                      <CardDescription className="line-clamp-2 text-[0.8125rem] leading-relaxed">
                        {assessment.description}
                      </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-wrap items-center gap-1.5">
                      <Badge variant="outline">
                        <Clock aria-hidden="true" />
                        <span className="sr-only">Perkiraan durasi: </span>
                        {assessment.estimated_minutes} menit
                      </Badge>
                      <Badge variant="outline">
                        <ListChecks aria-hidden="true" />
                        <span className="sr-only">Jumlah butir: </span>
                        {assessment.question_count} butir
                      </Badge>
                    </CardContent>
                  </Card>
                  <span
                    className="absolute right-0 bottom-0 grid h-11 w-12 place-items-center rounded-tl-[18px] rounded-br-3xl bg-background text-primary transition-colors duration-200 group-hover:bg-primary group-hover:text-on-primary"
                    aria-hidden="true"
                  >
                    <ArrowUpRight className="size-4.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                  </span>
                  <span className="sr-only">Lihat detail</span>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppShell>
  );
}
