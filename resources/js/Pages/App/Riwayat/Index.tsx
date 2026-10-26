import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, ClipboardCheck } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import { Button } from '@/Components/ui/button';

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
const TONES = ['bg-soft-peach', 'bg-soft-mint', 'bg-soft-lavender', 'bg-soft-sky'] as const;

const pillButton = 'rounded-full! px-5 text-sm';

/** SCR-015 */
export default function Index({ attempts, prev_page_url, next_page_url }: IndexProps) {
  return (
    <AppShell title="Riwayat asesmen" wide staggered>
      <div data-load>
        <Link
          href="/app/asesmen"
          className="group inline-flex min-h-11 items-center gap-2 text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-text"
        >
          <ArrowLeft
            className="size-4 transition-transform duration-200 group-hover:-translate-x-0.5"
            aria-hidden="true"
          />
          Asesmen
        </Link>
        <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">Riwayat asesmen</h1>
        <p className="mt-1 text-sm text-text-secondary">Hasil asesmenmu, dari yang terbaru.</p>
      </div>

      <div className="mt-4">
        {attempts.length === 0 ? (
          <div
            data-load="pop"
            className="mx-auto flex max-w-md flex-col items-center rounded-3xl bg-card p-5 text-center shadow-lift"
          >
            <img
              src="/images/asesmen/asesmen-kosong.webp"
              width={160}
              height={160}
              alt=""
              className="size-28 object-contain"
            />
            <p className="mt-3 max-w-sm text-sm text-text-secondary">
              Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu.
            </p>
            <Button asChild className={`mt-4 ${pillButton}`}>
              <Link href="/app/asesmen">Mulai asesmen</Link>
            </Button>
          </div>
        ) : (
          <>
            <ul className="grid gap-3 lg:grid-cols-2">
              {attempts.map((attempt, index) => (
                <li key={attempt.id} data-load>
                  <Link
                    href={`/app/riwayat/${attempt.id}`}
                    data-lift
                    data-press
                    className="group flex h-full items-center gap-3 rounded-3xl bg-card p-4 shadow-lift transition-shadow duration-300 hover:shadow-lift-hover"
                  >
                    <span
                      className={`grid size-12 shrink-0 place-items-center rounded-full text-primary ${TONES[index % TONES.length]}`}
                      aria-hidden="true"
                    >
                      <ClipboardCheck className="size-5" />
                    </span>
                    <span className="min-w-0 flex-1">
                      <span className="block text-[0.9375rem] leading-snug font-semibold">
                        {attempt.assessment_name}
                      </span>
                      <span className="block text-[0.8125rem] text-text-secondary">{attempt.completed_at}</span>
                      <span className="mt-1.5 flex flex-wrap gap-1.5">
                        {attempt.sub_scales.map((item) => (
                          <span key={item.name} className="rounded-full bg-neutral-soft px-2.5 py-0.5 text-xs text-text">
                            {item.name}: {item.interpretation}
                          </span>
                        ))}
                      </span>
                    </span>
                    <span
                      className="grid size-9 shrink-0 place-items-center rounded-full border-[1.5px] border-primary text-primary transition-colors duration-200 group-hover:bg-nav-hover group-hover:text-on-primary"
                      aria-hidden="true"
                    >
                      <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                    </span>
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
