import { useEffect, useState, type FormEvent, type ReactNode } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle } from 'lucide-react';
import FormField from '@/Components/shared/FormField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { Textarea } from '@/Components/ui/textarea';
import { postJson } from '@/lib/http';
import {
  ARTICLE_PROSE_CLASS,
  STATUS_TEXT,
  type ArticleFormData,
  type ArticleFormValues,
  type FormErrors,
} from '@/lib/adminArticle';

type ArticleFormProps = {
  definition: ArticleFormData;
  status: string | null;
};

const cardClass = 'rounded-xl border border-border bg-card p-5 shadow-card';

function Section({ id, title, intro, children }: { id: string; title: string; intro?: string; children: ReactNode }) {
  return (
    <section aria-labelledby={id} className={cardClass}>
      <h2 id={id} className="text-xl font-semibold">
        {title}
      </h2>
      {intro ? <p className="mt-1 text-text-secondary">{intro}</p> : null}
      <div className="mt-4 space-y-4">{children}</div>
    </section>
  );
}

/** SCR-026: formulir artikel (API-022 membuat, API-023 mengubah, API-024 pratinjau). Konten publik tanpa data pengguna. */
export default function ArticleForm({ definition, status }: ArticleFormProps) {
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

  return (
    <>
      <p>
        <Link
          href="/admin/artikel"
          className="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong underline underline-offset-4"
        >
          <ArrowLeft className="size-4" aria-hidden="true" />
          Semua artikel
        </Link>
      </p>

      <h1 className="mt-2">{isCreate ? 'Artikel baru' : 'Ubah artikel'}</h1>

      {status && STATUS_TEXT[status] ? (
        <Alert className="mt-4">
          <CheckCircle className="text-success!" aria-hidden="true" />
          <AlertDescription>{STATUS_TEXT[status]}</AlertDescription>
        </Alert>
      ) : null}

      {definition.is_demo ? (
        <Alert className="mt-4">
          <AlertDescription>
            Ini artikel contoh (slug berawalan demo-). Di produksi artikel contoh tidak tampil dan tidak boleh terbit.
          </AlertDescription>
        </Alert>
      ) : null}

      {definition.public_url ? (
        <p className="mt-4">
          <a
            href={definition.public_url}
            target="_blank"
            rel="noopener"
            className="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4"
          >
            Lihat di halaman Publik (tab baru)
          </a>
        </p>
      ) : null}

      {errorCount > 0 ? (
        <Alert variant="destructive" className="mt-4">
          <AlertDescription>Ada isian yang perlu diperbaiki. Periksa kolom yang bertanda galat.</AlertDescription>
        </Alert>
      ) : null}

      <form onSubmit={submit} noValidate className="mt-6 space-y-6">
        <Section id="sec-info" title="Info artikel">
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
            <div>
              <p className="text-sm text-text-secondary">Slug (tidak bisa diubah)</p>
              <p className="mt-1 font-medium">{data.slug}</p>
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
              <NativeSelect {...control} value={data.related_assessment_slug} onChange={text('related_assessment_slug')}>
                <option value="">Tidak ada</option>
                {definition.assessments.map((assessment) => (
                  <option key={assessment.slug} value={assessment.slug}>
                    {assessment.name} ({assessment.slug})
                  </option>
                ))}
              </NativeSelect>
            )}
          </FormField>
        </Section>

        <Section
          id="sec-body"
          title="Isi artikel"
          intro="Tulis dengan Markdown. Gunakan ## untuk subjudul. HTML mentah dibuang saat ditampilkan."
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
            <Button type="button" variant="outline" onClick={preview} disabled={previewing}>
              {previewing ? 'Memuat...' : 'Pratinjau isi'}
            </Button>
          </div>

          {previewError ? (
            <Alert variant="destructive">
              <AlertDescription>{previewError}</AlertDescription>
            </Alert>
          ) : null}

          {previewHtml !== null ? (
            <div aria-live="polite" className="rounded-lg border border-border bg-background p-5">
              <p className="mb-3 text-sm text-text-secondary">Pratinjau (tampilan isi di halaman Publik)</p>
              <div className={ARTICLE_PROSE_CLASS} dangerouslySetInnerHTML={{ __html: previewHtml }} />
            </div>
          ) : null}
        </Section>

        <Section
          id="sec-seo"
          title="SEO"
          intro="Opsional. Bila kosong, judul dan ringkasan dipakai (judul dipotong agar muat di hasil pencarian)."
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
        </Section>

        <Section
          id="sec-publish"
          title="Penerbitan"
          intro="Hanya artikel berstatus Terbit dengan tanggal yang sudah lewat yang tampil di halaman Publik."
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
        </Section>

        <div className="flex flex-wrap items-center gap-3">
          <Button type="submit" disabled={form.processing}>
            {form.processing ? 'Menyimpan...' : isCreate ? 'Buat artikel' : 'Simpan perubahan'}
          </Button>
          <Button type="button" variant="ghost" asChild>
            <Link href="/admin/artikel">Batal</Link>
          </Button>
        </div>
      </form>
    </>
  );
}
