import { Link } from '@inertiajs/react';
import { Clock, History, ListChecks, ArrowUpRight } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';

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
const TONES = ['bg-soft-peach', 'bg-soft-mint', 'bg-soft-lavender', 'bg-soft-sky'] as const;

/** SCR-012 */
export default function Index({ assessments }: IndexProps) {
  return (
    <AppShell title="Asesmen" wide staggered>
      <div data-load className="flex flex-wrap items-end justify-between gap-x-4 gap-y-1">
        <div>
          <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">Asesmen</h1>
          <p className="mt-1 max-w-prose text-sm text-text-secondary">
            Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.
          </p>
        </div>
        <Link
          href="/app/riwayat"
          data-press
          className="group inline-flex min-h-11 items-center gap-2 rounded-full border-[1.5px] border-primary bg-card px-4 text-[0.8125rem] font-medium text-primary transition-colors duration-200 hover:bg-primary hover:text-on-primary"
        >
          <History className="size-4" aria-hidden="true" />
          Lihat riwayat
        </Link>
      </div>

      <div className="mt-5">
        {assessments.length === 0 ? (
          <div
            data-load="pop"
            className="mx-auto flex max-w-md flex-col items-center rounded-2xl border border-border bg-card p-5 text-center shadow-lift"
          >
            <img
              src="/images/asesmen/asesmen-kosong.webp"
              width={160}
              height={160}
              alt=""
              className="size-28 object-contain"
            />
            <p className="mt-3 max-w-sm text-sm text-text-secondary">
              Belum ada asesmen yang tersedia. Coba lagi nanti, ya.
            </p>
          </div>
        ) : (
          <ul className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            {assessments.map((assessment, index) => (
              <li key={assessment.slug} data-load>
                <Link
                  href={`/app/asesmen/${assessment.slug}`}
                  data-lift
                  data-press
                  className={`group relative flex h-full min-h-40 flex-col rounded-3xl p-5 pb-14 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover ${TONES[index % TONES.length]}`}
                >
                  <span className="block text-base leading-snug font-bold">{assessment.name}</span>
                  <span className="mt-1 line-clamp-2 block text-[0.8125rem] leading-relaxed text-text-secondary">
                    {assessment.description}
                  </span>
                  <span className="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                    <span className="inline-flex items-center gap-1 rounded-full bg-card/60 px-2.5 py-1">
                      <Clock className="size-3.5" aria-hidden="true" />
                      <span className="sr-only">Perkiraan durasi: </span>
                      {assessment.estimated_minutes} menit
                    </span>
                    <span className="inline-flex items-center gap-1 rounded-full bg-card/60 px-2.5 py-1">
                      <ListChecks className="size-3.5" aria-hidden="true" />
                      <span className="sr-only">Jumlah butir: </span>
                      {assessment.question_count} butir
                    </span>
                  </span>
                  <span
                    className="absolute right-0 bottom-0 grid h-11 w-12 place-items-center rounded-tl-[18px] rounded-br-3xl bg-neutral-soft text-primary transition-colors duration-200 group-hover:bg-primary group-hover:text-on-primary"
                    aria-hidden="true"
                  >
                    <ArrowUpRight className="size-4.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                  </span>
                  <span className="sr-only">Mulai</span>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppShell>
  );
}
