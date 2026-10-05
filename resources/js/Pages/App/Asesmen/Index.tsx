import { Link } from '@inertiajs/react';
import { ClipboardCheck, Clock, History, ListChecks } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import EmptyState from '@/Components/shared/EmptyState';
import { Button } from '@/Components/ui/button';

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

/** SCR-012 */
export default function Index({ assessments }: IndexProps) {
  return (
    <AppShell title="Asesmen">
      <h1>Asesmen</h1>
      <p className="mt-2 max-w-prose text-text-secondary">
        Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.
      </p>

      <p className="mt-3">
        <Link
          href="/app/riwayat"
          className="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong underline underline-offset-4"
        >
          <History className="size-4" aria-hidden="true" />
          Lihat riwayat
        </Link>
      </p>

      <div className="mt-6">
        {assessments.length === 0 ? (
          <EmptyState icon={ClipboardCheck} message="Belum ada asesmen yang tersedia. Coba lagi nanti, ya." />
        ) : (
          <ul className="grid gap-4 md:grid-cols-2">
            {assessments.map((assessment) => (
              <li
                key={assessment.slug}
                className="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 shadow-card"
              >
                <div>
                  <h2 className="text-lg font-semibold">{assessment.name}</h2>
                  <p className="mt-2 text-text-secondary">{assessment.description}</p>
                </div>

                <dl className="flex flex-wrap gap-x-5 gap-y-1 text-sm text-text-secondary">
                  <div className="flex items-center gap-1.5">
                    <Clock className="size-4" aria-hidden="true" />
                    <dt className="sr-only">Perkiraan durasi</dt>
                    <dd>{assessment.estimated_minutes} menit</dd>
                  </div>
                  <div className="flex items-center gap-1.5">
                    <ListChecks className="size-4" aria-hidden="true" />
                    <dt className="sr-only">Jumlah butir</dt>
                    <dd>{assessment.question_count} butir</dd>
                  </div>
                </dl>

                <Button asChild className="mt-auto self-start">
                  <Link href={`/app/asesmen/${assessment.slug}`}>Mulai</Link>
                </Button>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppShell>
  );
}
