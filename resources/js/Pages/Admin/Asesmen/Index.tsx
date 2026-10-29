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
import DataTable, { type DataTableColumn } from '@/Components/shared/DataTable';
import DataTableCard from '@/Components/shared/DataTableCard';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import StatCard from '@/Components/shared/StatCard';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ROW_BUTTON } from '@/lib/adminTable';

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
    <Button asChild variant="outline" className={ROW_BUTTON}>
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
        className="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-text md:size-9"
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

const COLUMNS: DataTableColumn<AssessmentRow>[] = [
  { id: 'name', header: 'Asesmen', rowHeader: true, cell: (assessment) => <NameCell assessment={assessment} /> },
  { id: 'status', header: 'Status', cell: (assessment) => <StatusBadges assessment={assessment} /> },
  { id: 'questions', header: 'Butir', align: 'right', className: 'tabular-nums', cell: (assessment) => assessment.question_count },
  { id: 'rules', header: 'Aturan', align: 'right', className: 'tabular-nums', cell: (assessment) => assessment.rule_count },
  {
    id: 'attempts',
    header: 'Dikerjakan',
    align: 'right',
    className: 'font-semibold tabular-nums',
    cell: (assessment) => assessment.attempts,
  },
  { id: 'edit', header: 'Aksi', hideHeader: true, align: 'right', cell: (assessment) => <EditButton assessment={assessment} /> },
];

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

        {total > 0 ? (
          <dl data-load className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard label="Total Instrumen" value={String(total)} icon={ClipboardList} tone="lavender" />
            <StatCard label="Aktif" value={String(active)} icon={CircleCheck} tone="mint" />
            <StatCard label="Nonaktif" value={String(total - active)} icon={CircleOff} tone="peach" />
          </dl>
        ) : null}

        <DataTableCard
          id="daftar-instrumen"
          title="Daftar Instrumen"
          countLabel={total > 0 ? `${total} Instrumen` : undefined}
          isEmpty={total === 0}
          empty={{
            icon: ClipboardList,
            message: 'Belum ada Asesmen. Buat yang pertama, ya.',
            action: (
              <Button asChild className={PILL_BUTTON}>
                <Link href="/admin/asesmen/baru">
                  <Plus aria-hidden="true" />
                  Asesmen baru
                </Link>
              </Button>
            ),
          }}
        >
          <DataTable
            caption="Daftar Instrumen Asesmen"
            columns={COLUMNS}
            rows={assessments}
            getRowKey={(assessment) => assessment.id}
            mobileCard={(assessment) => (
              <>
                <NameCell assessment={assessment} />
                <StatusBadges assessment={assessment} />
                <p className="text-sm text-text-secondary">
                  {assessment.question_count} butir · {assessment.rule_count} aturan · dikerjakan {assessment.attempts}
                </p>
                <div>
                  <EditButton assessment={assessment} />
                </div>
              </>
            )}
          />
        </DataTableCard>
      </div>
    </AdminShell>
  );
}
