import { Link } from '@inertiajs/react';
import { ClipboardList, Plus } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import EmptyState from '@/Components/shared/EmptyState';
import { Button } from '@/Components/ui/button';

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

const badgeBase = 'inline-flex items-center rounded-md px-2 py-1 text-sm font-medium';

/** SCR-020: daftar Instrumen. Hanya definisi; tidak ada jawaban atau hasil pengguna. */
export default function Index({ assessments }: IndexProps) {
  return (
    <AdminShell title="Asesmen">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1>Asesmen</h1>
          <p className="mt-2 max-w-prose text-text-secondary">
            Kelola Instrumen, butir, pilihan jawaban, pengali, dan aturan skor. Instrumen tidak dihapus; nonaktifkan
            untuk menyembunyikannya. Jumlah pengerjaan di bawah 5 ditampilkan sebagai &quot;&lt;5&quot;.
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/asesmen/baru">
            <Plus aria-hidden="true" />
            Asesmen baru
          </Link>
        </Button>
      </div>

      <div className="mt-6">
        {assessments.length === 0 ? (
          <EmptyState
            icon={ClipboardList}
            message="Belum ada Asesmen. Buat yang pertama, ya."
            action={
              <Button asChild>
                <Link href="/admin/asesmen/baru">Asesmen baru</Link>
              </Button>
            }
          />
        ) : (
          <div className="overflow-x-auto rounded-xl border border-border bg-card">
            <table className="w-full min-w-[40rem] text-left">
              <caption className="sr-only">Daftar Instrumen Asesmen</caption>
              <thead className="border-b border-border text-sm text-text-secondary">
                <tr>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Asesmen
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    Status
                  </th>
                  <th scope="col" className="px-4 py-3 text-right font-medium">
                    Butir
                  </th>
                  <th scope="col" className="px-4 py-3 text-right font-medium">
                    Aturan
                  </th>
                  <th scope="col" className="px-4 py-3 text-right font-medium">
                    Dikerjakan
                  </th>
                  <th scope="col" className="px-4 py-3 font-medium">
                    <span className="sr-only">Aksi</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                {assessments.map((assessment) => (
                  <tr key={assessment.id} className="border-b border-border last:border-b-0">
                    <th scope="row" className="px-4 py-3 font-normal">
                      <span className="block font-medium">{assessment.name}</span>
                      <span className="block text-sm text-text-secondary">{assessment.slug}</span>
                    </th>
                    <td className="px-4 py-3">
                      <span className="flex flex-wrap gap-1">
                        <span
                          className={`${badgeBase} ${
                            assessment.is_active ? 'bg-neutral-soft text-brand-strong' : 'bg-muted text-text-secondary'
                          }`}
                        >
                          {assessment.is_active ? 'Aktif' : 'Nonaktif'}
                        </span>
                        {assessment.is_demo ? (
                          <span className={`${badgeBase} bg-muted text-text-secondary`}>Contoh</span>
                        ) : null}
                        {assessment.is_validated ? (
                          <span className={`${badgeBase} bg-neutral-soft text-brand-strong`}>Tervalidasi</span>
                        ) : null}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-right">{assessment.question_count}</td>
                    <td className="px-4 py-3 text-right">{assessment.rule_count}</td>
                    <td className="px-4 py-3 text-right">{assessment.attempts}</td>
                    <td className="px-4 py-3 text-right">
                      <Button asChild variant="outline">
                        <Link
                          href={`/admin/asesmen/${assessment.id}/ubah`}
                          aria-label={`Ubah ${assessment.name}`}
                        >
                          Ubah
                        </Link>
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </AdminShell>
  );
}
