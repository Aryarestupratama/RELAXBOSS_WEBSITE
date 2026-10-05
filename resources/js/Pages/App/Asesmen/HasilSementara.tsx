import { Link } from '@inertiajs/react';
import AppShell from '@/Layouts/AppShell';
import { Button } from '@/Components/ui/button';

type Result = {
  score: number;
  interpretation: string;
  severity_level: string;
  trigger_pfa: boolean;
};

type HasilSementaraProps = {
  assessmentName: string;
  results: Record<string, Result>;
};

// SEMENTARA (TASK-009): pengganti SCR-014 agar hasil skor bisa diperiksa. Diganti di TASK-010.
export default function HasilSementara({ assessmentName, results }: HasilSementaraProps) {
  return (
    <AppShell title="Hasil asesmen">
      <h1>Hasil: {assessmentName}</h1>
      <p className="mt-2 text-text-secondary">
        Tampilan sementara. Hasil ini bukan diagnosis; bila kamu butuh penilaian yang tepat, bicarakan dengan tenaga
        profesional.
      </p>

      <ul className="mt-6 grid gap-3 md:grid-cols-3">
        {Object.entries(results).map(([subScale, result]) => (
          <li key={subScale} className="rounded-xl border border-border bg-card p-4 shadow-card">
            <h2 className="text-base font-semibold">{subScale}</h2>
            <p className="mt-1 text-xl font-semibold">{result.interpretation}</p>
            <p className="text-sm text-text-secondary">Skor {result.score}</p>
          </li>
        ))}
      </ul>

      <Button asChild className="mt-6">
        <Link href="/app/asesmen">Kembali ke asesmen</Link>
      </Button>
    </AppShell>
  );
}
