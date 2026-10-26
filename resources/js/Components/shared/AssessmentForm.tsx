import { useEffect, type FormEvent, type ReactNode } from 'react';
import { Link, useForm } from '@inertiajs/react';
import {
  CheckCircle,
  CircleAlert,
  Gauge,
  Info,
  ListChecks,
  LoaderCircle,
  Save,
  ShieldCheck,
  SlidersHorizontal,
  FileText,
  type LucideIcon,
} from 'lucide-react';
import AssessmentOptionsEditor from '@/Components/shared/AssessmentOptionsEditor';
import AssessmentQuestionsEditor from '@/Components/shared/AssessmentQuestionsEditor';
import AssessmentRulesEditor from '@/Components/shared/AssessmentRulesEditor';
import FormField from '@/Components/shared/FormField';
import PageHeader from '@/Components/shared/PageHeader';
import SwitchField from '@/Components/shared/SwitchField';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Separator } from '@/Components/ui/separator';
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

type SectionTone = 'lavender' | 'sky' | 'mint' | 'peach';

const ICON_TONE: Record<SectionTone, string> = {
  lavender: 'bg-soft-lavender',
  sky: 'bg-soft-sky',
  mint: 'bg-soft-mint',
  peach: 'bg-soft-peach',
};

type SectionProps = {
  id: string;
  title: string;
  intro?: string;
  icon: LucideIcon;
  tone: SectionTone;
  /** Penanda kecil di kanan judul (mis. jumlah butir). */
  aside?: ReactNode;
  children: ReactNode;
};

/** Satu bagian formulir = satu `Card`: kepala berikon, `Separator`, lalu isi. */
function Section({ id, title, intro, icon: Icon, tone, aside, children }: SectionProps) {
  return (
    <section id={id} aria-labelledby={`${id}-title`} className="scroll-mt-6">
      <Card className="gap-0">
        <CardHeader className="flex-row items-start gap-3 p-5 md:p-6">
          <span
            className={cn('grid size-11 shrink-0 place-items-center rounded-full text-text', ICON_TONE[tone])}
            aria-hidden="true"
          >
            <Icon className="size-5" />
          </span>
          <div className="min-w-0 flex-1">
            <h2 id={`${id}-title`} className="text-lg leading-snug font-bold tracking-tight">
              {title}
            </h2>
            {intro ? <CardDescription className="mt-0.5">{intro}</CardDescription> : null}
          </div>
          {aside}
        </CardHeader>
        <Separator />
        <CardContent className="space-y-5 p-5 md:p-6">{children}</CardContent>
      </Card>
    </section>
  );
}

const SECTION_LINKS: { id: string; label: string; icon: LucideIcon }[] = [
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
            <Alert role="note" className={cn(NOTE_ALERT, 'border-l-warning')}>
              <Info className="text-warning!" aria-hidden="true" />
              <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">
                Asesmen ini sudah dikerjakan ({definition.attempts} kali). Hasil lama tidak berubah. Tetapi bila kamu
                mengubah pilihan jawaban, pengali, atau butir, jawaban lama tidak lagi sebanding dengan versi baru.
              </AlertDescription>
            </Alert>
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
        <nav aria-label="Bagian formulir" className="hidden lg:sticky lg:top-6 lg:block">
          <Card className="gap-1 p-2">
            <p className="px-3 pt-2 pb-1 text-xs font-semibold tracking-wide text-text-secondary">Di halaman ini</p>
            <ul>
              {SECTION_LINKS.map(({ id, label, icon: Icon }) => (
                <li key={id}>
                  <a
                    href={`#${id}`}
                    className="flex min-h-11 items-center gap-2.5 rounded-xl px-3 text-sm font-medium transition-colors hover:bg-neutral-soft"
                  >
                    <Icon className="size-4 text-text-secondary" aria-hidden="true" />
                    {label}
                  </a>
                </li>
              ))}
            </ul>
          </Card>
        </nav>

        <form onSubmit={submit} noValidate className="min-w-0 space-y-6">
          <Section id="sec-info" title="Info Asesmen" icon={FileText} tone="lavender">
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
              <div className="rounded-2xl bg-neutral-soft px-4 py-3">
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
          </Section>

          <Section
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
          </Section>

          <Section
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
          </Section>

          <Section
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
          </Section>

          <Section
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
          </Section>

          {/* Bar simpan menempel di bawah; di HP naik di atas bar tab AdminShell. */}
          <div className="sticky bottom-20 z-30 md:bottom-4">
            <Card className="flex-row flex-wrap items-center justify-between gap-3 rounded-2xl bg-card/90 px-4 py-3 shadow-popover backdrop-blur">
              <p
                aria-live="polite"
                className={cn('text-sm font-medium', errorCount > 0 ? 'text-destructive' : 'text-text-secondary')}
              >
                {statusText}
              </p>
              <div className="flex items-center gap-2">
                <Button type="button" variant="ghost" className="rounded-full! px-5 text-sm" asChild>
                  <Link href="/admin/asesmen">Batal</Link>
                </Button>
                <Button type="submit" disabled={form.processing} className="rounded-full! px-6 text-sm">
                  {form.processing ? (
                    <LoaderCircle className="animate-spin motion-reduce:animate-none" aria-hidden="true" />
                  ) : (
                    <Save aria-hidden="true" />
                  )}
                  {form.processing ? 'Menyimpan...' : isCreate ? 'Buat Asesmen' : 'Simpan perubahan'}
                </Button>
              </div>
            </Card>
          </div>
        </form>
      </div>
    </>
  );
}
