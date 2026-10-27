import { Link } from '@inertiajs/react';
import {
  BadgeCheck,
  CircleCheck,
  CircleOff,
  ClipboardCheck,
  ClipboardList,
  FlaskConical,
  Pencil,
  Plus,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import StatCard from '@/Components/shared/StatCard';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

type AssessmentRow = {
  id: number;
  slug: string;
  name: string;
  is_active: boolean;
  is_demo: boolean;
  is_validated: boolean;
  question_count: number;
  rule_count: number;
  attempts: string;
  sort_order: number;
};

type IndexProps = {
  assessments: AssessmentRow[];
};

const NUM_HEAD = 'text-right';
const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';
const PILL_BUTTON = 'rounded-full! px-5 text-sm';

function StatusBadges({ assessment }: { assessment: AssessmentRow }) {
  return (
    <span className="flex flex-wrap gap-1.5">
      {assessment.is_active ? (
        <Badge variant="mint">
          <CircleCheck aria-hidden="true" />
          Aktif
        </Badge>
      ) : (
        <Badge variant="secondary">
          <CircleOff aria-hidden="true" />
          Nonaktif
        </Badge>
      )}
      {assessment.is_demo ? (
        <Badge variant="peach">
          <FlaskConical aria-hidden="true" />
          Contoh
        </Badge>
      ) : null}
      {assessment.is_validated ? (
        <Badge variant="sky">
          <BadgeCheck aria-hidden="true" />
          Tervalidasi
        </Badge>
      ) : null}
    </span>
  );
}

function EditButton({ assessment }: { assessment: AssessmentRow }) {
  return (
    <Button asChild variant="outline" className={PILL_BUTTON}>
      <Link href={`/admin/asesmen/${assessment.id}/ubah`} aria-label={`Ubah ${assessment.name}`}>
        <Pencil className="size-4!" aria-hidden="true" />
        Ubah
      </Link>
    </Button>
  );
}

function NameCell({ assessment }: { assessment: AssessmentRow }) {
  return (
    <span className="flex items-center gap-3">
      <span
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-text"
        aria-hidden="true"
      >
        <ClipboardCheck className="size-5" />
      </span>
      <span className="min-w-0">
        <span className="block font-semibold">{assessment.name}</span>
        <span className="block text-xs text-text-secondary">{assessment.slug}</span>
      </span>
    </span>
  );
}

/** SCR-020: daftar Instrumen. Hanya definisi; tidak ada jawaban atau hasil pengguna. */
export default function Index({ assessments }: IndexProps) {
  const total = assessments.length;
  const active = assessments.filter((assessment) => assessment.is_active).length;

  return (
    <AdminShell title="Asesmen" staggered>
      <PageHeader
        title="Asesmen"
        description="Kelola Instrumen, butir, pilihan jawaban, pengali, dan aturan skor."
        actions={
          <Button asChild className={PILL_BUTTON}>
            <Link href="/admin/asesmen/baru">
              <Plus aria-hidden="true" />
              Asesmen baru
            </Link>
          </Button>
        }
      />

      <div className="flex flex-col gap-6">
        <div data-load>
          <InlineNote>Instrumen tidak dihapus, hanya dinonaktifkan, dan jumlah pengerjaan di bawah 5 tampil sebagai &quot;&lt;5&quot;.</InlineNote>
        </div>

        {total === 0 ? (
          <Card tone="lavender" data-load className="items-center gap-4 px-6 py-10 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <ClipboardList className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">Belum ada Asesmen. Buat yang pertama, ya.</p>
            <Button asChild className={PILL_BUTTON}>
              <Link href="/admin/asesmen/baru">
                <Plus aria-hidden="true" />
                Asesmen baru
              </Link>
            </Button>
          </Card>
        ) : (
          <>
            <dl data-load className="grid gap-4 sm:grid-cols-3">
              <StatCard label="Total Instrumen" value={String(total)} icon={ClipboardList} tone="lavender" />
              <StatCard label="Aktif" value={String(active)} icon={CircleCheck} tone="mint" />
              <StatCard label="Nonaktif" value={String(total - active)} icon={CircleOff} tone="peach" />
            </dl>

            <section aria-labelledby="daftar-instrumen" data-load className="flex flex-col gap-3">
              <div className="flex items-center justify-between gap-3">
                <h2 id="daftar-instrumen" className="text-lg leading-snug font-bold tracking-tight">
                  Daftar Instrumen
                </h2>
                <Badge variant="outline">{total} Instrumen</Badge>
              </div>

              {/* >= 768px: tabel */}
              <Card className="hidden gap-0 p-3 md:flex">
                <Table className="min-w-[40rem]">
                  <TableCaption className="sr-only">Daftar Instrumen Asesmen</TableCaption>
                  <TableHeader>
                    <TableRow className="border-0 bg-soft-lavender/40 hover:bg-soft-lavender/40 [&>th:first-child]:rounded-l-xl [&>th:last-child]:rounded-r-xl">
                      <TableHead>Asesmen</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead className={NUM_HEAD}>Butir</TableHead>
                      <TableHead className={NUM_HEAD}>Aturan</TableHead>
                      <TableHead className={NUM_HEAD}>Dikerjakan</TableHead>
                      <TableHead>
                        <span className="sr-only">Aksi</span>
                      </TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {assessments.map((assessment) => (
                      <TableRow key={assessment.id}>
                        <TableHead scope="row" className={ROW_HEAD}>
                          <NameCell assessment={assessment} />
                        </TableHead>
                        <TableCell>
                          <StatusBadges assessment={assessment} />
                        </TableCell>
                        <TableCell className="text-right tabular-nums">{assessment.question_count}</TableCell>
                        <TableCell className="text-right tabular-nums">{assessment.rule_count}</TableCell>
                        <TableCell className="text-right font-semibold tabular-nums">{assessment.attempts}</TableCell>
                        <TableCell className="text-right">
                          <EditButton assessment={assessment} />
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </Card>

              {/* < 768px: kartu */}
              <ul className="grid gap-3 md:hidden">
                {assessments.map((assessment) => (
                  <li key={assessment.id}>
                    <Card className="gap-3 p-4">
                      <NameCell assessment={assessment} />
                      <StatusBadges assessment={assessment} />
                      <p className="text-sm text-text-secondary">
                        {assessment.question_count} butir · {assessment.rule_count} aturan · dikerjakan{' '}
                        {assessment.attempts}
                      </p>
                      <div>
                        <EditButton assessment={assessment} />
                      </div>
                    </Card>
                  </li>
                ))}
              </ul>
            </section>
          </>
        )}
      </div>
    </AdminShell>
  );
}
