import AdminShell from '@/Layouts/AdminShell';
import AnnouncementForm from '@/Components/shared/AnnouncementForm';
import type { AnnouncementFormData } from '@/lib/adminAnnouncement';

type FormProps = {
  form: AnnouncementFormData;
};

/** SCR-028: buat dan ubah pengumuman. `key` memuat ulang formulir setelah simpan. */
export default function Form({ form }: FormProps) {
  return (
    <AdminShell title={form.mode === 'create' ? 'Pengumuman baru' : 'Ubah pengumuman'} staggered>
      <AnnouncementForm key={`${form.mode}-${form.id ?? 'new'}-${form.version}`} definition={form} />
    </AdminShell>
  );
}
