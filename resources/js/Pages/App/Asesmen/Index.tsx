import { Link } from '@inertiajs/react';
import { ClipboardCheck, Clock, History, ListChecks } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ActionTile from '@/Components/shared/ActionTile';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';

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
              <li key={assessment.slug}>
                <ActionTile
                  icon={ClipboardCheck}
                  title={assessment.name}
                  description={assessment.description}
                  href={`/app/asesmen/${assessment.slug}`}
                  tone={TONES[index % TONES.length]}
                >
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
                </ActionTile>
              </li>
            ))}
          </ul>
        )}
      </div>
    </AppShell>
  );
}
