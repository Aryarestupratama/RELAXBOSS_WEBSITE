import { useEffect, useState, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import {
  CalendarClock,
  CircleAlert,
  ExternalLink,
  Eye,
  FileText,
  FlaskConical,
  LoaderCircle,
  PenLine,
  Search,
} from 'lucide-react';
import FormField from '@/Components/shared/FormField';
import FormSection, { FormSaveBar, FormSectionNav } from '@/Components/shared/FormSection';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { Textarea } from '@/Components/ui/textarea';
import { postJson } from '@/lib/http';
import {
  ARTICLE_PROSE_CLASS,
  type ArticleFormData,
  type ArticleFormValues,
  type FormErrors,
} from '@/lib/adminArticle';
import { cn } from '@/lib/utils';

type ArticleFormProps = {
  definition: ArticleFormData;
};

const NOTE_ALERT = 'rounded-2xl border-transparent border-l-4 px-4 py-3 shadow-card';

const SECTION_LINKS = [
  { id: 'sec-info', label: 'Info artikel', icon: FileText },
  { id: 'sec-body', label: 'Isi artikel', icon: PenLine },
  { id: 'sec-seo', label: 'SEO', icon: Search },
  { id: 'sec-publish', label: 'Penerbitan', icon: CalendarClock },
];

/** SCR-026: formulir artikel (API-022 membuat, API-023 mengubah, API-024 pratinjau). Konten publik tanpa data pengguna. */
export default function ArticleForm({ definition }: ArticleFormProps) {
  const form = useForm<ArticleFormValues>(definition.values);
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
      form.post('/admin/artikel');
    } else {
      form.put(`/admin/artikel/${definition.id}`);
    }
  };

  const text = (field: keyof ArticleFormValues) => (event: { target: { value: string } }) =>
    form.setData(field, event.target.value);

  const preview = async () => {
    setPreviewing(true);
    setPreviewError(null);

    const result = await postJson<{ html: string }>('/admin/artikel/pratinjau', { body_markdown: data.body_markdown });

    if (result.ok) {
      setPreviewHtml(result.data.html);
    } else {
      setPreviewHtml(null);
      setPreviewError(result.status === 422 ? 'Isi artikel masih kosong atau terlalu panjang.' : result.message);
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
          ? 'Isi semua bagian, lalu buat artikel.'
          : 'Belum ada perubahan.';

  return (
    <>
      <PageHeader
        back={{ href: '/admin/artikel', label: 'Semua artikel' }}
        title={isCreate ? 'Artikel baru' : 'Ubah artikel'}
        description="Atur info, isi, SEO, dan penerbitan artikel."
        actions={
          definition.public_url ? (
            <Button asChild variant="outline" className="rounded-full! px-5 text-sm">
              <a href={definition.public_url} target="_blank" rel="noopener">
                <ExternalLink className="size-4!" aria-hidden="true" />
                Lihat di halaman Publik (tab baru)
              </a>
            </Button>
          ) : undefined
        }
      />

      {definition.is_demo || errorCount > 0 ? (
        <div data-load className="mb-6 space-y-3">
          {definition.is_demo ? (
            <InlineNote tone="warning" icon={FlaskConical}>Ini artikel contoh (slug berawalan demo-), jadi tidak tampil dan tidak boleh terbit di produksi.</InlineNote>
          ) : null}

          {errorCount > 0 ? (
            <Alert variant="destructive" className={cn(NOTE_ALERT, 'border-l-destructive')}>
              <CircleAlert aria-hidden="true" />
              <AlertTitle>Ada isian yang perlu diperbaiki</AlertTitle>
              <AlertDescription>Periksa kolom yang bertanda galat.</AlertDescription>
            </Alert>
          ) : null}
        </div>
      ) : null}

      <div data-load className="grid gap-6 lg:grid-cols-[13rem_minmax(0,1fr)] lg:items-start">
        <FormSectionNav links={SECTION_LINKS} />

        <form onSubmit={submit} noValidate className="min-w-0 space-y-6">
          <FormSection id="sec-info" title="Info artikel" icon={FileText} tone="lavender">
            {isCreate ? (
              <FormField
                id="slug"
                label="Slug"
                hint="Huruf kecil, angka, dan tanda hubung. Dipakai di alamat halaman dan tidak bisa diubah setelah dibuat."
                error={errors.slug}
              >
                {(control) => <Input {...control} maxLength={120} value={data.slug} onChange={text('slug')} />}
              </FormField>
            ) : (
              <div className="rounded-2xl border border-border/60 bg-card px-4 py-3">
                <p className="text-[0.8125rem] text-text-secondary">Slug (tidak bisa diubah)</p>
                <p className="mt-0.5 font-semibold">{data.slug}</p>
              </div>
            )}

            <FormField id="title" label="Judul" error={errors.title}>
              {(control) => <Input {...control} maxLength={150} value={data.title} onChange={text('title')} />}
            </FormField>

            <FormField
              id="excerpt"
              label="Ringkasan"
              hint={`Tampil di daftar artikel dan jadi deskripsi bawaan. ${data.excerpt.length}/300 karakter.`}
              error={errors.excerpt}
            >
              {(control) => <Textarea {...control} maxLength={300} value={data.excerpt} onChange={text('excerpt')} />}
            </FormField>

            <div className="grid gap-4 sm:grid-cols-2">
              <FormField id="category" label="Kategori" error={errors.category}>
                {(control) => (
                  <NativeSelect {...control} value={data.category} onChange={text('category')}>
                    {definition.categories.map((category) => (
                      <option key={category.value} value={category.value}>
                        {category.label}
                      </option>
                    ))}
                  </NativeSelect>
                )}
              </FormField>

              <FormField id="author_name" label="Nama penulis" error={errors.author_name}>
                {(control) => (
                  <Input {...control} maxLength={100} value={data.author_name} onChange={text('author_name')} />
                )}
              </FormField>
            </div>

            <FormField
              id="related_assessment_slug"
              label="Asesmen terkait (opsional)"
              hint="Tampil sebagai tautan di bawah artikel, hanya bila Asesmennya aktif dan boleh tampil Publik."
              error={errors.related_assessment_slug}
            >
              {(control) => (
                <NativeSelect
                  {...control}
                  value={data.related_assessment_slug}
                  onChange={text('related_assessment_slug')}
                >
                  <option value="">Tidak ada</option>
                  {definition.assessments.map((assessment) => (
                    <option key={assessment.slug} value={assessment.slug}>
                      {assessment.name} ({assessment.slug})
                    </option>
                  ))}
                </NativeSelect>
              )}
            </FormField>
          </FormSection>

          <FormSection
            id="sec-body"
            title="Isi artikel"
            intro="Tulis dengan Markdown. Gunakan ## untuk subjudul. HTML mentah dibuang saat ditampilkan."
            icon={PenLine}
            tone="sky"
            aside={<Badge variant="outline">{data.body_markdown.length.toLocaleString('id-ID')} karakter</Badge>}
          >
            <FormField id="body_markdown" label="Isi (Markdown)" error={errors.body_markdown}>
              {(control) => (
                <Textarea
                  {...control}
                  rows={18}
                  className="min-h-72 font-mono text-base"
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
                  <p className="text-sm text-text-secondary">Tampilan isi di halaman Publik</p>
                </div>
                <div
                  className={ARTICLE_PROSE_CLASS}
                  dangerouslySetInnerHTML={{
                    __html: previewHtml,
                  }}
                />
              </div>
            ) : null}
          </FormSection>

          <FormSection
            id="sec-seo"
            title="SEO"
            intro="Opsional. Bila kosong, judul dan ringkasan dipakai (judul dipotong agar muat di hasil pencarian)."
            icon={Search}
            tone="mint"
          >
            <FormField
              id="meta_title"
              label="Judul SEO"
              hint={`Maksimal 60 karakter. ${data.meta_title.length}/60.`}
              error={errors.meta_title}
            >
              {(control) => <Input {...control} maxLength={60} value={data.meta_title} onChange={text('meta_title')} />}
            </FormField>

            <FormField
              id="meta_description"
              label="Deskripsi SEO"
              hint={`Maksimal 160 karakter. ${data.meta_description.length}/160.`}
              error={errors.meta_description}
            >
              {(control) => (
                <Textarea
                  {...control}
                  maxLength={160}
                  value={data.meta_description}
                  onChange={text('meta_description')}
                />
              )}
            </FormField>
          </FormSection>

          <FormSection
            id="sec-publish"
            title="Penerbitan"
            intro="Hanya artikel berstatus Terbit dengan tanggal yang sudah lewat yang tampil di halaman Publik."
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
                hint="Kosongkan agar terbit saat disimpan. Tanggal di masa depan menjadwalkan artikel."
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
            cancelHref="/admin/artikel"
            submitLabel={isCreate ? 'Buat artikel' : 'Simpan perubahan'}
          />
        </form>
      </div>
    </>
  );
}
