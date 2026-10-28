import { useEffect, useState, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { CalendarClock, CircleAlert, Eye, LoaderCircle, Megaphone } from 'lucide-react';
import FormField from '@/Components/shared/FormField';
import FormSection, { FormSaveBar, FormSectionNav } from '@/Components/shared/FormSection';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { Textarea } from '@/Components/ui/textarea';
import { ARTICLE_PROSE_CLASS, type FormErrors } from '@/lib/adminArticle';
import type { AnnouncementFormData, AnnouncementFormValues } from '@/lib/adminAnnouncement';
import { postJson } from '@/lib/http';
import { cn } from '@/lib/utils';

type AnnouncementFormProps = {
  definition: AnnouncementFormData;
};

const NOTE_ALERT = 'rounded-2xl border-transparent border-l-4 px-4 py-3 shadow-card';

const SECTION_LINKS = [
  { id: 'sec-isi', label: 'Isi pengumuman', icon: Megaphone },
  { id: 'sec-publish', label: 'Penerbitan', icon: CalendarClock },
];

/** SCR-028: formulir pengumuman (FR-038). Konten global tanpa data pengguna. */
export default function AnnouncementForm({ definition }: AnnouncementFormProps) {
  const form = useForm<AnnouncementFormValues>(definition.values);
  const errors = form.errors as FormErrors;
  const errorCount = Object.keys(errors).length;
  const isCreate = definition.mode === 'create';
  const { data } = form;

  const [previewHtml, setPreviewHtml] = useState<string | null>(null);
  const [previewError, setPreviewError] = useState<string | null>(null);
  const [previewing, setPreviewing] = useState(false);

  // Galat validasi: fokus pindah ke kolom pertama yang salah (Design bagian 5).
  useEffect(() => {
    if (errorCount > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [errors, errorCount]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (isCreate) {
      form.post('/admin/pengumuman');
    } else {
      form.put(`/admin/pengumuman/${definition.id}`);
    }
  };

  const text = (field: keyof AnnouncementFormValues) => (event: { target: { value: string } }) =>
    form.setData(field, event.target.value);

  const preview = async () => {
    setPreviewing(true);
    setPreviewError(null);

    const result = await postJson<{ html: string }>('/admin/pengumuman/pratinjau', {
      body_markdown: data.body_markdown,
    });

    if (result.ok) {
      setPreviewHtml(result.data.html);
    } else {
      setPreviewHtml(null);
      setPreviewError(result.status === 422 ? 'Isi pengumuman masih kosong atau terlalu panjang.' : result.message);
    }

    setPreviewing(false);
  };

  const statusText = form.processing
    ? 'Menyimpan...'
    : errorCount > 0
      ? `${errorCount} isian perlu diperbaiki.`
      : form.isDirty
        ? 'Ada perubahan yang belum disimpan.'
        : isCreate
          ? 'Isi semua bagian, lalu buat pengumuman.'
          : 'Belum ada perubahan.';

  return (
    <>
      <PageHeader
        back={{ href: '/admin/pengumuman', label: 'Semua pengumuman' }}
        title={isCreate ? 'Pengumuman baru' : 'Ubah pengumuman'}
        description="Tulis kabar pembaruan singkat yang dibaca pengguna di lonceng."
      />

      {errorCount > 0 ? (
        <div data-load className="mb-6">
          <Alert variant="destructive" className={cn(NOTE_ALERT, 'border-l-destructive')}>
            <CircleAlert aria-hidden="true" />
            <AlertTitle>Ada isian yang perlu diperbaiki</AlertTitle>
            <AlertDescription>Periksa kolom yang bertanda galat.</AlertDescription>
          </Alert>
        </div>
      ) : null}

      <div data-load className="grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start">
        <FormSectionNav links={SECTION_LINKS} />

        <form onSubmit={submit} noValidate className="min-w-0 space-y-6">
          <FormSection
            id="sec-isi"
            title="Isi pengumuman"
            intro="Singkat dan jelas, mis. apa yang baru dan di mana melihatnya. Markdown dasar boleh dipakai; HTML mentah dibuang."
            icon={Megaphone}
            tone="lavender"
            aside={
              <Badge variant="outline">
                {data.body_markdown.length.toLocaleString('id-ID')}/{definition.body_max_chars.toLocaleString('id-ID')}
              </Badge>
            }
          >
            <FormField id="title" label="Judul" error={errors.title}>
              {(control) => <Input {...control} maxLength={120} value={data.title} onChange={text('title')} />}
            </FormField>

            <FormField id="body_markdown" label="Isi (Markdown)" error={errors.body_markdown}>
              {(control) => (
                <Textarea
                  {...control}
                  rows={8}
                  maxLength={definition.body_max_chars}
                  className="min-h-40 font-mono text-base"
                  value={data.body_markdown}
                  onChange={text('body_markdown')}
                />
              )}
            </FormField>

            <div>
              <Button
                type="button"
                variant="outline"
                className="rounded-full! px-5 text-sm"
                onClick={preview}
                disabled={previewing}
              >
                {previewing ? (
                  <LoaderCircle className="animate-spin motion-reduce:animate-none" aria-hidden="true" />
                ) : (
                  <Eye aria-hidden="true" />
                )}
                {previewing ? 'Memuat...' : 'Pratinjau isi'}
              </Button>
            </div>

            {previewError ? (
              <Alert variant="destructive" className="rounded-2xl">
                <CircleAlert aria-hidden="true" />
                <AlertDescription>{previewError}</AlertDescription>
              </Alert>
            ) : null}

            {previewHtml !== null ? (
              <div aria-live="polite" className="rounded-2xl border border-border bg-surface-alt p-5 md:p-6">
                <div className="mb-4 flex flex-wrap items-center gap-2">
                  <Badge variant="lavender">
                    <Eye aria-hidden="true" />
                    Pratinjau
                  </Badge>
                  <p className="text-sm text-text-secondary">Isi seperti yang dibaca pengguna</p>
                </div>
                <div className={ARTICLE_PROSE_CLASS} dangerouslySetInnerHTML={{ __html: previewHtml }} />
              </div>
            ) : null}
          </FormSection>

          <FormSection
            id="sec-publish"
            title="Penerbitan"
            intro="Hanya pengumuman berstatus Terbit dengan tanggal yang sudah lewat yang terbaca pengguna."
            icon={CalendarClock}
            tone="peach"
          >
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField id="status" label="Status" error={errors.status}>
                {(control) => (
                  <NativeSelect {...control} value={data.status} onChange={text('status')}>
                    {definition.statuses.map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </NativeSelect>
                )}
              </FormField>

              <FormField
                id="published_at"
                label="Tanggal terbit (WIB)"
                hint="Kosongkan agar terbit saat disimpan. Tanggal di masa depan menjadwalkan pengumuman."
                error={errors.published_at}
              >
                {(control) => (
                  <Input {...control} type="datetime-local" value={data.published_at} onChange={text('published_at')} />
                )}
              </FormField>
            </div>
          </FormSection>

          <FormSaveBar
            statusText={statusText}
            hasErrors={errorCount > 0}
            processing={form.processing}
            cancelHref="/admin/pengumuman"
            submitLabel={isCreate ? 'Buat pengumuman' : 'Simpan perubahan'}
          />
        </form>
      </div>
    </>
  );
}
