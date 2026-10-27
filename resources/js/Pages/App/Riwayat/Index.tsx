import { Link } from '@inertiajs/react';
import { ArrowRight, ClipboardCheck, ClipboardList } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import EmptyState from '@/Components/shared/EmptyState';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { severityOf, type SeverityLevel } from '@/lib/assessment';

type SummaryItem = {
  id: number;
  assessment_name: string;
  completed_at: string;
  sub_scales: { name: string; interpretation: string; severity_level: string }[];
};

type IndexProps = {
  attempts: SummaryItem[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

/** Pastel bergantian untuk kotak ikon (teks di atasnya `--text`, RULE-052). */
const ICON_TONES = ['bg-soft-peach', 'bg-soft-mint', 'bg-soft-lavender', 'bg-soft-sky'] as const;

/** Tingkat hasil ke varian Badge. Teks tingkat selalu ikut tampil, jadi makna tidak hanya warna. */
const SEVERITY_BADGE: Record<SeverityLevel, 'mint' | 'sky' | 'lavender' | 'peach'> = {
  normal: 'mint',
  mild: 'sky',
  moderate: 'lavender',
  severe: 'peach',
};

const pillButton = 'rounded-full! px-5 text-sm';

/** SCR-015 */
export default function Index({ attempts, prev_page_url, next_page_url }: IndexProps) {
  return (
    <AppShell title="Riwayat asesmen" wide staggered>
      <PageHeader
        back={{ href: '/app/asesmen', label: 'Asesmen' }}
        title="Riwayat asesmen"
        description="Hasil asesmenmu, dari yang terbaru."
      />

      <div>
        {attempts.length === 0 ? (
          <Card data-load="pop" className="mx-auto max-w-md px-6 py-8">
            <EmptyState
              icon={ClipboardList}
              title="Belum ada riwayat"
              message="Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu."
              action={
                <Button asChild className={pillButton}>
                  <Link href="/app/asesmen">Mulai asesmen</Link>
                </Button>
              }
            />
          </Card>
        ) : (
          <>
            <ul className="grid gap-3 lg:grid-cols-2">
              {attempts.map((attempt, index) => (
                <li key={attempt.id} data-load>
                  <Link
                    href={`/app/riwayat/${attempt.id}`}
                    data-lift
                    data-press
                    className="group block h-full rounded-3xl transition-shadow duration-300 hover:shadow-lift-hover"
                  >
                    <Card className="h-full flex-row items-center gap-3 p-4 transition-shadow duration-300 group-hover:shadow-lift-hover">
                      <span
                        className={`grid size-12 shrink-0 place-items-center rounded-full text-primary ${ICON_TONES[index % ICON_TONES.length]}`}
                        aria-hidden="true"
                      >
                        <ClipboardCheck className="size-5" />
                      </span>
                      <div className="flex min-w-0 flex-1 flex-col gap-2">
                        <CardHeader className="gap-0.5">
                          <CardTitle className="text-[0.9375rem]">{attempt.assessment_name}</CardTitle>
                          <CardDescription className="text-[0.8125rem]">{attempt.completed_at}</CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-wrap gap-1.5">
                          {attempt.sub_scales.map((item) => (
                            <Badge
                              key={item.name}
                              variant={SEVERITY_BADGE[severityOf(item.severity_level)]}
                              className="whitespace-normal"
                            >
                              {item.name}: {item.interpretation}
                            </Badge>
                          ))}
                        </CardContent>
                      </div>
                      <span
                        className="grid size-9 shrink-0 place-items-center rounded-full border-[1.5px] border-primary text-primary transition-colors duration-200 group-hover:bg-nav-hover group-hover:text-on-primary"
                        aria-hidden="true"
                      >
                        <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                      </span>
                    </Card>
                    <span className="sr-only">Lihat hasil {attempt.assessment_name}</span>
                  </Link>
                </li>
              ))}
            </ul>

            {prev_page_url || next_page_url ? (
              <nav aria-label="Halaman riwayat" className="mt-5 flex justify-between gap-3">
                {prev_page_url ? (
                  <Button asChild variant="outline" className={pillButton}>
                    <Link href={prev_page_url}>Sebelumnya</Link>
                  </Button>
                ) : (
                  <span />
                )}
                {next_page_url ? (
                  <Button asChild variant="outline" className={pillButton}>
                    <Link href={next_page_url}>Berikutnya</Link>
                  </Button>
                ) : null}
              </nav>
            ) : null}
          </>
        )}
      </div>
    </AppShell>
  );
}
