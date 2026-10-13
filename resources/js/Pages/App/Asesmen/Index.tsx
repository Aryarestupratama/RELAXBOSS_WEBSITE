import { Link } from '@inertiajs/react';
import { ArrowRight, Clock, History, ListChecks } from 'lucide-react';
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

/** Warna kotak thumbnail bergantian: kuning, hijau, ungu muda, persik (token pastel, teks `--text`). */
const THUMB_TONES = ['bg-mood-yellow', 'bg-mood-green', 'bg-brand-soft', 'bg-mood-peach'] as const;

/** SCR-012 */
export default function Index({ assessments }: IndexProps) {
  return (
    <AppShell title="Asesmen" staggered>
      <div data-load>
        <div className="flex flex-wrap items-end justify-between gap-x-4 gap-y-2">
          <h1>Asesmen</h1>
          <Link
            href="/app/riwayat"
            className="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong underline underline-offset-4"
          >
            <History className="size-4" aria-hidden="true" />
            Lihat riwayat
          </Link>
        </div>
        <p className="mt-2 max-w-prose text-text-secondary">
          Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.
        </p>
      </div>

      <div className="mt-8">
        {assessments.length === 0 ? (
          <div
            data-load="pop"
            className="mx-auto flex max-w-xl flex-col items-center rounded-xl border border-border bg-card p-8 text-center shadow-card"
          >
            <img
              src="/images/asesmen/asesmen-kosong.webp"
              width={160}
              height={160}
              alt=""
              className="size-40 object-contain"
            />
            <p className="mt-4 max-w-sm text-lg text-text-secondary">
              Belum ada asesmen yang tersedia. Coba lagi nanti, ya.
            </p>
          </div>
        ) : (
          <ul className="grid gap-4 lg:grid-cols-2 lg:gap-5">
            {assessments.map((assessment, index) => (
              <li key={assessment.slug} data-load>
                <Link
                  href={`/app/asesmen/${assessment.slug}`}
                  data-lift
                  className="group flex h-full items-center gap-4 rounded-xl border border-border bg-card p-4 shadow-card transition-shadow hover:shadow-popover sm:gap-5 sm:p-5"
                >
                  <span
                    className={`grid size-20 shrink-0 place-items-center rounded-xl sm:size-22 ${THUMB_TONES[index % THUMB_TONES.length]}`}
                    aria-hidden="true"
                  >
                    <span className="grid size-14 place-items-center rounded-full bg-card sm:size-16">
                      <img
                        src="/images/landing/asesmen-checklist.webp"
                        width={48}
                        height={48}
                        alt=""
                        className="size-10 object-contain sm:size-12"
                      />
                    </span>
                  </span>

                  <span className="min-w-0 flex-1">
                    <span className="block text-lg font-semibold">{assessment.name}</span>
                    <span className="mt-1 line-clamp-2 block text-text-secondary">{assessment.description}</span>
                    <span className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-text-secondary">
                      <span className="inline-flex items-center gap-1.5">
                        <Clock className="size-4" aria-hidden="true" />
                        <span className="sr-only">Perkiraan durasi: </span>
                        {assessment.estimated_minutes} menit
                      </span>
                      <span className="inline-flex items-center gap-1.5">
                        <ListChecks className="size-4" aria-hidden="true" />
                        <span className="sr-only">Jumlah butir: </span>
                        {assessment.question_count} butir
                      </span>
                    </span>
                  </span>

                  <span
                    className="grid size-11 shrink-0 place-items-center rounded-full bg-primary text-on-primary transition-transform motion-safe:group-hover:translate-x-0.5"
                  >
                    <ArrowRight className="size-5" aria-hidden="true" />
                    <span className="sr-only">Mulai</span>
                  </span>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppShell>
  );
}
