import AdminShell from '@/Layouts/AdminShell';
import ArticleForm from '@/Components/shared/ArticleForm';
import type { ArticleFormData } from '@/lib/adminArticle';

type FormProps = {
  form: ArticleFormData;
  status: string | null;
};

/** SCR-026: buat dan ubah artikel. `key` memuat ulang formulir setelah simpan. */
export default function Form({ form, status }: FormProps) {
  return (
    <AdminShell title={form.mode === 'create' ? 'Artikel baru' : 'Ubah artikel'} staggered>
      <ArticleForm key={`${form.mode}-${form.id ?? 'new'}-${form.version}`} definition={form} status={status} />
    </AdminShell>
  );
}
