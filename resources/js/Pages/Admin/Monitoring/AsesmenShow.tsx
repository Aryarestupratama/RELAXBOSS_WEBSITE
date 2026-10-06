import { Link } from '@inertiajs/react';
import AdminShell from '@/Layouts/AdminShell';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringItem from '@/Components/shared/MonitoringItem';
import { Button } from '@/Components/ui/button';
import { formatDate, type SubScaleResult } from '@/lib/adminMonitoring';

type AsesmenShowProps = {
  attempt: {
    pseudo_id: string;
    date: string;
    assessment: string;
    sub_scales: SubScaleResult[];
    recommendation: string | null;
  };
};

/**
 * SCR-022, detail hasil Asesmen: hasil per subskala dan Rekomendasi AI. Tanpa jawaban per butir dan tanpa
 * jawaban PFA (RULE-042). Membuka halaman ini sudah tercatat di server.
 */
export default function AsesmenShow({ attempt }: AsesmenShowProps) {
  return (
    <AdminShell title={`Hasil ${attempt.pseudo_id}`}>
      <MonitoringHeader current="asesmen" />

      <div className="mt-6 space-y-4">
        <Button asChild variant="ghost">
          <Link href="/admin/monitoring/asesmen">Kembali ke daftar</Link>
        </Button>

        <h2>
          Hasil <span className="font-mono">{attempt.pseudo_id}</span>
        </h2>
        <dl className="grid gap-x-6 gap-y-1 text-text-secondary sm:grid-cols-2">
          <div className="flex gap-2">
            <dt className="font-medium">Asesmen:</dt>
            <dd>{attempt.assessment}</dd>
          </div>
          <div className="flex gap-2">
            <dt className="font-medium">Tanggal:</dt>
            <dd>{formatDate(attempt.date)}</dd>
          </div>
        </dl>
        <p className="text-sm text-text-secondary">
          Pembukaan ini sudah dicatat. Jawaban per butir dan jawaban PFA tidak ditampilkan untuk admin.
        </p>

        <div className="overflow-x-auto rounded-xl border border-border bg-card">
          <table className="w-full min-w-[32rem] text-left">
            <caption className="sr-only">Hasil per subskala</caption>
            <thead className="border-b border-border text-sm text-text-secondary">
              <tr>
                <th scope="col" className="px-4 py-3 font-medium">
                  Subskala
                </th>
                <th scope="col" className="px-4 py-3 font-medium">
                  Skor
                </th>
                <th scope="col" className="px-4 py-3 font-medium">
                  Tingkat
                </th>
                <th scope="col" className="px-4 py-3 font-medium">
                  Interpretasi
                </th>
              </tr>
            </thead>
            <tbody>
              {attempt.sub_scales.map((scale) => (
                <tr key={scale.name} className="border-b border-border last:border-b-0">
                  <th scope="row" className="px-4 py-3 font-medium">
                    {scale.name}
                  </th>
                  <td className="px-4 py-3">{scale.score}</td>
                  <td className="px-4 py-3">{scale.severity}</td>
                  <td className="px-4 py-3">{scale.interpretation}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <ul>
          <MonitoringItem title="Rekomendasi AI">
            {attempt.recommendation ?? 'Belum ada Rekomendasi AI untuk hasil ini.'}
          </MonitoringItem>
        </ul>
      </div>
    </AdminShell>
  );
}
