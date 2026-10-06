import { useEffect, type FormEvent, type ReactNode } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle } from 'lucide-react';
import AssessmentOptionsEditor from '@/Components/shared/AssessmentOptionsEditor';
import AssessmentQuestionsEditor from '@/Components/shared/AssessmentQuestionsEditor';
import AssessmentRulesEditor from '@/Components/shared/AssessmentRulesEditor';
import CheckboxField from '@/Components/shared/CheckboxField';
import FormField from '@/Components/shared/FormField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import {
  STATUS_TEXT,
  scoreRange,
  subScalesOf,
  toNumberOrEmpty,
  type AssessmentFormData,
  type AssessmentFormValues,
  type FormErrors,
} from '@/lib/adminAssessment';

type AssessmentFormProps = {
  definition: AssessmentFormData;
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

/**
 * SCR-020: formulir Instrumen (API-017 membuat, API-018 mengubah). Admin hanya mengelola definisi Instrumen,
 * tidak pernah melihat jawaban atau hasil pengguna (RULE-042).
 */
export default function AssessmentForm({ definition, status }: AssessmentFormProps) {
  const form = useForm<AssessmentFormValues>(definition.values);
  const errors = form.errors as FormErrors;
  const errorCount = Object.keys(errors).length;
  const isCreate = definition.mode === 'create';
  const { data } = form;

  // Galat validasi: fokus pindah ke kolom pertama yang salah (Design bagian 5).
  useEffect(() => {
    if (errorCount > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [errors, errorCount]);

  const subScales = subScalesOf(data.questions);
  const ranges = Object.fromEntries(
    subScales.map((name) => [name, scoreRange(data.questions, data.options, data.score_multiplier, name)]),
  );

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (isCreate) {
      form.post('/admin/asesmen');
    } else {
      form.put(`/admin/asesmen/${definition.id}`);
    }
  };

  const text = (field: keyof AssessmentFormValues) => (event: { target: { value: string } }) =>
    form.setData(field, event.target.value as never);

  return (
    <>
      <p>
        <Link
          href="/admin/asesmen"
          className="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong underline underline-offset-4"
        >
          <ArrowLeft className="size-4" aria-hidden="true" />
          Semua Asesmen
        </Link>
      </p>

      <h1 className="mt-2">{isCreate ? 'Asesmen baru' : 'Ubah Asesmen'}</h1>

      {status && STATUS_TEXT[status] ? (
        <Alert className="mt-4">
          <CheckCircle className="text-success!" aria-hidden="true" />
          <AlertDescription>{STATUS_TEXT[status]}</AlertDescription>
        </Alert>
      ) : null}

      {definition.has_attempts ? (
        <Alert className="mt-4">
          <AlertDescription>
            Asesmen ini sudah dikerjakan ({definition.attempts} kali). Hasil lama tidak berubah. Tetapi bila kamu
            mengubah pilihan jawaban, pengali, atau butir, jawaban lama tidak lagi sebanding dengan versi baru.
          </AlertDescription>
        </Alert>
      ) : null}

      {errorCount > 0 ? (
        <Alert variant="destructive" className="mt-4">
          <AlertDescription>Ada isian yang perlu diperbaiki. Periksa kolom yang bertanda galat.</AlertDescription>
        </Alert>
      ) : null}

      <form onSubmit={submit} noValidate className="mt-6 space-y-6">
        <Section id="sec-info" title="Info Asesmen">
          {isCreate ? (
            <FormField
              id="slug"
              label="Slug"
              hint="Huruf kecil, angka, dan tanda hubung. Dipakai di alamat halaman dan tidak bisa diubah setelah dibuat."
              error={errors.slug}
            >
              {(control) => <Input {...control} maxLength={80} value={data.slug} onChange={text('slug')} />}
            </FormField>
          ) : (
            <div>
              <p className="text-sm text-text-secondary">Slug (tidak bisa diubah)</p>
              <p className="mt-1 font-medium">{data.slug}</p>
            </div>
          )}

          <FormField id="name" label="Nama resmi" error={errors.name}>
            {(control) => <Input {...control} maxLength={150} value={data.name} onChange={text('name')} />}
          </FormField>

          <FormField
            id="display_name"
            label="Nama ramah (opsional)"
            hint="Nama yang dilihat pengguna. Bila kosong, nama resmi yang dipakai."
            error={errors.display_name}
          >
            {(control) => (
              <Input {...control} maxLength={150} value={data.display_name} onChange={text('display_name')} />
            )}
          </FormField>

          <FormField id="description" label="Deskripsi" error={errors.description}>
            {(control) => (
              <Textarea {...control} maxLength={2000} value={data.description} onChange={text('description')} />
            )}
          </FormField>

          <FormField id="instructions" label="Petunjuk pengerjaan (opsional)" error={errors.instructions}>
            {(control) => (
              <Textarea {...control} maxLength={2000} value={data.instructions} onChange={text('instructions')} />
            )}
          </FormField>

          <div className="grid gap-4 sm:grid-cols-2">
            <FormField id="estimated_minutes" label="Perkiraan durasi (menit)" error={errors.estimated_minutes}>
              {(control) => (
                <Input
                  {...control}
                  type="number"
                  inputMode="numeric"
                  min={1}
                  value={data.estimated_minutes}
                  onChange={(event) => form.setData('estimated_minutes', toNumberOrEmpty(event.target.value))}
                />
              )}
            </FormField>

            <FormField
              id="sort_order"
              label="Urutan tampil"
              hint="Angka kecil tampil lebih dulu."
              error={errors.sort_order}
            >
              {(control) => (
                <Input
                  {...control}
                  type="number"
                  inputMode="numeric"
                  min={0}
                  value={data.sort_order}
                  onChange={(event) => form.setData('sort_order', toNumberOrEmpty(event.target.value))}
                />
              )}
            </FormField>
          </div>

          <CheckboxField
            id="is_active"
            label="Aktif (tampil dan bisa dikerjakan pengguna)"
            checked={data.is_active}
            onChange={(checked) => form.setData('is_active', checked)}
            hint="Asesmen tidak dihapus. Untuk menyembunyikannya, nonaktifkan."
            error={errors.is_active}
          />
        </Section>

        <Section
          id="sec-options"
          title="Skala jawaban"
          intro="Satu skala dipakai untuk semua butir. Nilai terendah dan tertinggi dipakai untuk membalik butir terbalik."
        >
          <AssessmentOptionsEditor
            options={data.options}
            maxOptions={definition.limits.max_options}
            errors={errors}
            onChange={(options) => form.setData('options', options)}
          />

          <FormField
            id="score_multiplier"
            label="Pengali skor"
            hint="Jumlah nilai per subskala dikali angka ini lalu dibulatkan. Isi 1 bila tidak ada pengali."
            error={errors.score_multiplier}
          >
            {(control) => (
              <Input
                {...control}
                type="number"
                inputMode="decimal"
                step="0.01"
                min={0.01}
                max={99.99}
                value={data.score_multiplier}
                onChange={text('score_multiplier')}
              />
            )}
          </FormField>
        </Section>

        <Section
          id="sec-questions"
          title="Butir"
          intro="Setiap butir masuk ke satu subskala. Subskala yang sama ditulis persis sama."
        >
          <AssessmentQuestionsEditor
            questions={data.questions}
            subScales={subScales}
            maxQuestions={definition.limits.max_questions}
            errors={errors}
            onChange={(questions) => form.setData('questions', questions)}
          />
        </Section>

        <Section
          id="sec-rules"
          title="Aturan skor"
          intro="Rentang skor per subskala bersifat inklusif, tidak boleh tumpang tindih, dan harus menutup seluruh kemungkinan skor."
        >
          <AssessmentRulesEditor
            rules={data.rules}
            subScales={subScales}
            ranges={ranges}
            severityLevels={definition.severity_levels}
            maxRules={definition.limits.max_rules}
            errors={errors}
            onChange={(rules) => form.setData('rules', rules)}
          />
        </Section>

        <Section
          id="sec-source"
          title="Sumber dan validasi"
          intro="Isi hanya yang sudah terkonfirmasi. Status tervalidasi tampil untuk pengguna bila tanggal validasi terisi."
        >
          <FormField id="source_reference" label="Sumber dan izin (opsional)" error={errors.source_reference}>
            {(control) => (
              <Input {...control} maxLength={255} value={data.source_reference} onChange={text('source_reference')} />
            )}
          </FormField>

          <div className="grid gap-4 sm:grid-cols-2">
            <FormField id="creator_name" label="Nama pembuat (opsional)" error={errors.creator_name}>
              {(control) => (
                <Input {...control} maxLength={150} value={data.creator_name} onChange={text('creator_name')} />
              )}
            </FormField>

            <FormField id="creator_institution" label="Institusi pembuat (opsional)" error={errors.creator_institution}>
              {(control) => (
                <Input
                  {...control}
                  maxLength={150}
                  value={data.creator_institution}
                  onChange={text('creator_institution')}
                />
              )}
            </FormField>

            <FormField id="validator_name" label="Nama validator (opsional)" error={errors.validator_name}>
              {(control) => (
                <Input {...control} maxLength={150} value={data.validator_name} onChange={text('validator_name')} />
              )}
            </FormField>

            <FormField
              id="validator_credential"
              label="Kredensial validator (opsional)"
              error={errors.validator_credential}
            >
              {(control) => (
                <Input
                  {...control}
                  maxLength={150}
                  value={data.validator_credential}
                  onChange={text('validator_credential')}
                />
              )}
            </FormField>
          </div>

          <FormField id="validated_at" label="Tanggal validasi (opsional)" error={errors.validated_at}>
            {(control) => <Input {...control} type="date" value={data.validated_at} onChange={text('validated_at')} />}
          </FormField>
        </Section>

        <div className="flex flex-wrap items-center gap-3">
          <Button type="submit" disabled={form.processing}>
            {form.processing ? 'Menyimpan...' : isCreate ? 'Buat Asesmen' : 'Simpan perubahan'}
          </Button>
          <Button type="button" variant="ghost" asChild>
            <Link href="/admin/asesmen">Batal</Link>
          </Button>
        </div>
      </form>
    </>
  );
}
