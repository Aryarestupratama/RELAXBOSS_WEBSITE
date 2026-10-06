import AdminShell from '@/Layouts/AdminShell';
import AssessmentForm from '@/Components/shared/AssessmentForm';
import type { AssessmentFormData } from '@/lib/adminAssessment';

type FormProps = {
  form: AssessmentFormData;
  status: string | null;
};

/** SCR-020: buat dan ubah Asesmen. `key` memuat ulang formulir setelah simpan agar ID butir baru ikut terbaca. */
export default function Form({ form, status }: FormProps) {
  return (
    <AdminShell title={form.mode === 'create' ? 'Asesmen baru' : 'Ubah Asesmen'}>
      <AssessmentForm key={`${form.mode}-${form.id ?? 'new'}-${form.version}`} definition={form} status={status} />
    </AdminShell>
  );
}
