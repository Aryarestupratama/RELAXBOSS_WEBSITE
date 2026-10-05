import { Link } from '@inertiajs/react';
import { ChevronRight, ClipboardCheck } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import EmptyState from '@/Components/shared/EmptyState';
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

/** SCR-015 */
export default function Index({ attempts, prev_page_url, next_page_url }: IndexProps) {
  return (
    <AppShell title="Riwayat asesmen">
      <h1>Riwayat asesmen</h1>
      <p className="mt-2 text-text-secondary">Hasil asesmenmu, dari yang terbaru.</p>

      <div className="mt-6">
        {attempts.length === 0 ? (
          <EmptyState
            icon={ClipboardCheck}
            message="Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu."
            action={
              <Button asChild>
                <Link href="/app/asesmen">Mulai asesmen</Link>
              </Button>
            }
          />
        ) : (
          <>
            <ul className="grid gap-3">
              {attempts.map((attempt) => (
                <li key={attempt.id}>
                  <Link
                    href={`/app/riwayat/${attempt.id}`}
                    className="flex min-h-11 items-center justify-between gap-3 rounded-xl border border-border bg-card p-4 shadow-card transition-colors hover:bg-neutral-soft"
                  >
                    <span className="min-w-0">
                      <span className="block font-semibold">{attempt.assessment_name}</span>
                      <span className="block text-sm text-text-secondary">{attempt.completed_at}</span>
                      <span className="mt-2 flex flex-wrap gap-2">
                        {attempt.sub_scales.map((item) => (
                          <span
                            key={item.name}
                            className="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text"
                          >
                            {item.name}: {item.interpretation}
                          </span>
                        ))}
                      </span>
                    </span>
                    <ChevronRight className="size-5 shrink-0 text-text-secondary" aria-hidden="true" />
                  </Link>
                </li>
              ))}
            </ul>

            {prev_page_url || next_page_url ? (
              <nav aria-label="Halaman riwayat" className="mt-6 flex justify-between gap-3">
                {prev_page_url ? (
                  <Button asChild variant="outline">
                    <Link href={prev_page_url}>Sebelumnya</Link>
                  </Button>
                ) : (
                  <span />
                )}
                {next_page_url ? (
                  <Button asChild variant="outline">
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
