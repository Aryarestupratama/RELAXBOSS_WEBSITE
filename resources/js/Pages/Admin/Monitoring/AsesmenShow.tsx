import { CalendarDays, ClipboardList, Lock } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import InlineNote from '@/Components/shared/InlineNote';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import MonitoringItem from '@/Components/shared/MonitoringItem';
import MonitoringMeta from '@/Components/shared/MonitoringMeta';
import { Badge } from '@/Components/ui/badge';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
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

const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-semibold tracking-normal whitespace-normal text-text';

/**
 * SCR-022, detail hasil Asesmen: hasil per subskala dan Rekomendasi AI. Tanpa jawaban per butir dan tanpa
 * jawaban PFA (RULE-042). Membuka halaman ini sudah tercatat di server.
 */
export default function AsesmenShow({ attempt }: AsesmenShowProps) {
  return (
    <AdminShell title={`Hasil ${attempt.pseudo_id}`} staggered>
      <MonitoringHeader current="asesmen" back={{ href: '/admin/monitoring/asesmen', label: 'Kembali ke daftar' }} />

      <div className="flex flex-col gap-6">
        <MonitoringMeta
          title={
            <>
              Hasil <span className="font-mono">{attempt.pseudo_id}</span>
            </>
          }
          note="Pembukaan ini sudah dicatat."
          items={[
            { icon: ClipboardList, label: 'Asesmen', value: attempt.assessment },
            { icon: CalendarDays, label: 'Tanggal', value: formatDate(attempt.date) },
          ]}
        />

        <div data-load>
          <InlineNote>Jawaban per butir dan jawaban PFA tidak ditampilkan untuk admin.</InlineNote>
        </div>

        <section aria-labelledby="hasil-subskala" data-load className="flex flex-col gap-3">
          <div className="flex items-center justify-between gap-3">
            <h2 id="hasil-subskala" className="text-lg leading-snug font-bold tracking-tight">
              Hasil per subskala
            </h2>
            <Badge variant="outline">{attempt.sub_scales.length} subskala</Badge>
          </div>

          <Card className="gap-0 p-3">
            <Table className="min-w-[32rem]">
              <TableCaption className="sr-only">Hasil per subskala</TableCaption>
              <TableHeader>
                <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                  <TableHead>Subskala</TableHead>
                  <TableHead>Skor</TableHead>
                  <TableHead>Tingkat</TableHead>
                  <TableHead>Interpretasi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {attempt.sub_scales.map((scale) => (
                  <TableRow key={scale.name}>
                    <TableHead scope="row" className={ROW_HEAD}>
                      {scale.name}
                    </TableHead>
                    <TableCell className="font-semibold tabular-nums">{scale.score}</TableCell>
                    <TableCell>
                      <Badge variant="sky">{scale.severity}</Badge>
                    </TableCell>
                    <TableCell className="min-w-48 text-text-secondary">{scale.interpretation}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </Card>
        </section>

        <section aria-labelledby="rekomendasi-ai" data-load className="flex flex-col gap-3">
          <div className="flex items-center justify-between gap-3">
            <h2 id="rekomendasi-ai" className="text-lg leading-snug font-bold tracking-tight">
              Rekomendasi AI
            </h2>
            <Badge variant="outline">
              <Lock aria-hidden="true" />
              Hanya baca
            </Badge>
          </div>
          <ul>
            <MonitoringItem title="Rekomendasi AI" kind="ai">
              {attempt.recommendation ?? 'Belum ada Rekomendasi AI untuk hasil ini.'}
            </MonitoringItem>
          </ul>
        </section>
      </div>
    </AdminShell>
  );
}
