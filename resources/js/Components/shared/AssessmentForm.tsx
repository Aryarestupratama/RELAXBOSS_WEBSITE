import { useEffect, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { CheckCircle, CircleAlert, FileText, Gauge, ListChecks, ShieldCheck, SlidersHorizontal } from 'lucide-react';
import AssessmentOptionsEditor from '@/Components/shared/AssessmentOptionsEditor';
import AssessmentQuestionsEditor from '@/Components/shared/AssessmentQuestionsEditor';
import AssessmentRulesEditor from '@/Components/shared/AssessmentRulesEditor';
import FormField from '@/Components/shared/FormField';
import FormSection, { FormSaveBar, FormSectionNav } from '@/Components/shared/FormSection';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import SwitchField from '@/Components/shared/SwitchField';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
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
import { cn } from '@/lib/utils';

type AssessmentFormProps = {
  definition: AssessmentFormData;
  status: string | null;
};

const SECTION_LINKS = [
  { id: 'sec-info', label: 'Info Asesmen', icon: FileText },
  { id: 'sec-options', label: 'Skala jawaban', icon: SlidersHorizontal },
  { id: 'sec-questions', label: 'Butir', icon: ListChecks },
  { id: 'sec-rules', label: 'Aturan skor', icon: Gauge },
  { id: 'sec-source', label: 'Sumber dan validasi', icon: ShieldCheck },
];

const NOTE_ALERT = 'rounded-2xl border-transparent border-l-4 px-4 py-3 shadow-card';

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

  const statusText = form.processing
    ? 'Menyimpan...'
    : errorCount > 0
      ? `${errorCount} isian perlu diperbaiki.`
      : form.isDirty
        ? 'Ada perubahan yang belum disimpan.'
        : isCreate
          ? 'Isi semua bagian, lalu buat Asesmen.'
          : 'Belum ada perubahan.';

  return (
    <>
      <PageHeader
        back={{ href: '/admin/asesmen', label: 'Semua Asesmen' }}
        title={isCreate ? 'Asesmen baru' : 'Ubah Asesmen'}
        description="Atur info, skala jawaban, butir, aturan skor, dan sumber Instrumen."
      />

      {(status && STATUS_TEXT[status]) || definition.has_attempts || errorCount > 0 ? (
        <div data-load className="mb-6 space-y-3">
          {status && STATUS_TEXT[status] ? (
            <Alert className={cn(NOTE_ALERT, 'border-l-success')}>
              <CheckCircle className="text-success!" aria-hidden="true" />
              <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">
                {STATUS_TEXT[status]}
              </AlertDescription>
            </Alert>
          ) : null}

          {definition.has_attempts ? (
            <InlineNote tone="warning">Asesmen ini sudah dikerjakan {definition.attempts} kali, jadi mengubah pilihan, pengali, atau butir membuat jawaban lama tak lagi sebanding.</InlineNote>
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
          <FormSection id="sec-info" title="Info Asesmen" icon={FileText} tone="lavender">
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
              <div className="rounded-2xl border border-border/60 bg-card px-4 py-3">
                <p className="text-[0.8125rem] text-text-secondary">Slug (tidak bisa diubah)</p>
                <p className="mt-0.5 font-semibold">{data.slug}</p>
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

            <SwitchField
              id="is_active"
              label="Aktif (tampil dan bisa dikerjakan pengguna)"
              hint="Asesmen tidak dihapus. Untuk menyembunyikannya, nonaktifkan."
              checked={data.is_active}
              onChange={(checked) => form.setData('is_active', checked)}
              error={errors.is_active}
            />
          </FormSection>

          <FormSection
            id="sec-options"
            title="Skala jawaban"
            intro="Satu skala dipakai untuk semua butir. Nilai terendah dan tertinggi dipakai untuk membalik butir terbalik."
            icon={SlidersHorizontal}
            tone="sky"
            aside={<Badge variant="outline">{data.options.length} pilihan</Badge>}
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
          </FormSection>

          <FormSection
            id="sec-questions"
            title="Butir"
            intro="Setiap butir masuk ke satu subskala. Subskala yang sama ditulis persis sama."
            icon={ListChecks}
            tone="mint"
            aside={
              <Badge variant="outline">
                {data.questions.length} dari {definition.limits.max_questions}
              </Badge>
            }
          >
            <AssessmentQuestionsEditor
              questions={data.questions}
              subScales={subScales}
              maxQuestions={definition.limits.max_questions}
              errors={errors}
              onChange={(questions) => form.setData('questions', questions)}
            />
          </FormSection>

          <FormSection
            id="sec-rules"
            title="Aturan skor"
            intro="Rentang skor per subskala bersifat inklusif, tidak boleh tumpang tindih, dan harus menutup seluruh kemungkinan skor."
            icon={Gauge}
            tone="peach"
            aside={
              <Badge variant="outline">
                {data.rules.length} dari {definition.limits.max_rules}
              </Badge>
            }
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
          </FormSection>

          <FormSection
            id="sec-source"
            title="Sumber dan validasi"
            intro="Isi hanya yang sudah terkonfirmasi. Status tervalidasi tampil untuk pengguna bila tanggal validasi terisi."
            icon={ShieldCheck}
            tone="lavender"
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
          </FormSection>

          <FormSaveBar
            statusText={statusText}
            hasErrors={errorCount > 0}
            processing={form.processing}
            cancelHref="/admin/asesmen"
            submitLabel={isCreate ? 'Buat Asesmen' : 'Simpan perubahan'}
          />
        </form>
      </div>
    </>
  );
}
