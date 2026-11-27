import { useState, type FormEvent } from 'react';
import { Download, FileSpreadsheet, LoaderCircle, ShieldAlert } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import FormField from '@/Components/shared/FormField';
import InlineNote from '@/Components/shared/InlineNote';
import MonitoringHeader from '@/Components/shared/MonitoringHeader';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Separator } from '@/Components/ui/separator';
import {
  EXPORT_NOTE,
  EXPORT_WARNING,
  datasetHint,
  datasetLabel,
  describeCounts,
  type ExportDataset,
  type ExportRange,
} from '@/lib/adminExport';
import { postForFile, saveBlob } from '@/lib/http';
import { toast } from '@/lib/toast';
import { cn } from '@/lib/utils';

type EksporProps = {
  /** Fitur yang mendukung Excel, mis. `chat` dan `assessment`. */
  datasets: string[];
  /** Jumlah pengguna yang menyetujui pelatihan, sudah disamarkan bila kelompok kecil. */
  consented_users: string;
  /** Tanggal hari ini (WIB, Y-m-d): batas atas pemilih tanggal. */
  today: string;
};

const ALL = 'all';

const CHOICE =
  'relative flex min-h-12 cursor-pointer items-start gap-3 rounded-2xl border-[1.5px] px-4 py-3 text-sm text-text transition-all duration-200 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring';

type ChoiceProps = { id: string; value: string; selected: boolean; title: string; hint?: string | null; disabled: boolean };

/** Kartu pilihan radio (pola sama dengan LikertScale): terpilih ditandai garis, latar, dan teks tebal, bukan hanya warna. */
function Choice({ id, value, selected, title, hint, disabled }: ChoiceProps) {
  return (
    <label
      htmlFor={id}
      className={cn(
        CHOICE,
        selected ? 'border-primary bg-soft-lavender shadow-card' : 'border-border bg-card hover:border-primary/40 hover:bg-soft-lavender/50',
        disabled && 'cursor-not-allowed opacity-60',
      )}
    >
      <RadioGroupItem id={id} value={value} className="mt-0.5" />
      <span className="min-w-0">
        <span className={cn('block', selected && 'font-semibold')}>{title}</span>
        {hint ? <span className="mt-0.5 block text-[0.8125rem] text-text-secondary">{hint}</span> : null}
      </span>
    </label>
  );
}

/** SCR-022 (TASK-057, FR-030): ekspor dataset AI ke Excel. Pilihan waktu hanya semua data atau rentang tanggal. */
export default function Ekspor({ datasets, consented_users, today }: EksporProps) {
  const [dataset, setDataset] = useState<ExportDataset>(ALL);
  const [range, setRange] = useState<ExportRange>('all');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [processing, setProcessing] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const options = datasets.length > 1 ? [ALL, ...datasets] : datasets;

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (processing) {
      return;
    }

    const errors: Record<string, string> = {};
    if (range === 'custom') {
      if (!from) {
        errors.from = 'Isi tanggal mulai.';
      }
      if (!to) {
        errors.to = 'Isi tanggal akhir.';
      } else if (from && to < from) {
        errors.to = 'Tanggal akhir tidak boleh sebelum tanggal mulai.';
      }
    }
    setFieldErrors(errors);
    setFormError(null);
    if (Object.keys(errors).length > 0) {
      return;
    }

    setProcessing(true);
    const result = await postForFile('/admin/monitoring/ekspor', { dataset, range, from: from || null, to: to || null });
    setProcessing(false);

    if (!result.ok) {
      setFieldErrors(result.fieldErrors);
      setFormError(result.message);
      return;
    }

    saveBlob(result.blob, result.filename);
    const summary = describeCounts(result.headers.get('X-Export-Counts'));
    toast.success(summary ? `Berkas diunduh: ${summary}.` : 'Berkas diunduh.');
  };

  return (
    <AdminShell title="Ekspor data" staggered>
      <div className="mx-auto w-full max-w-3xl min-w-0">
        <MonitoringHeader current="ekspor" />

        <div className="flex flex-col gap-4">
          <div data-load>
            <InlineNote icon={ShieldAlert} tone="warning">
              {EXPORT_WARNING}
            </InlineNote>
          </div>

          <Card data-load role="region" aria-labelledby="ekspor-title" className="gap-5 p-5">
            <CardHeader>
              <CardTitle id="ekspor-title" role="heading" aria-level={2} className="flex items-center gap-2 text-base">
                <FileSpreadsheet className="size-5 text-primary" aria-hidden="true" />
                Ekspor ke Excel
              </CardTitle>
              <CardDescription className="text-[0.8125rem]">
                Pengguna yang menyetujui pelatihan: {consented_users}. {EXPORT_NOTE}
              </CardDescription>
            </CardHeader>
            <Separator />

            <form onSubmit={(event) => void submit(event)} noValidate className="flex flex-col gap-6">
              {formError ? (
                <Alert variant="destructive" role="alert">
                  <AlertDescription>{formError}</AlertDescription>
                </Alert>
              ) : null}

              <CardContent className="space-y-6 p-0">
                <fieldset className="space-y-2">
                  <legend className="mb-2 text-sm font-semibold">Dataset</legend>
                  <RadioGroup
                    className="gap-2!"
                    name="dataset"
                    aria-label="Dataset"
                    value={dataset}
                    onValueChange={setDataset}
                    disabled={processing}
                  >
                    {options.map((value) => (
                      <Choice
                        key={value}
                        id={`dataset-${value}`}
                        value={value}
                        selected={dataset === value}
                        title={value === ALL ? 'Semua dataset' : datasetLabel(value)}
                        hint={value === ALL ? 'Chatbot dan Asesmen dalam satu berkas.' : datasetHint(value)}
                        disabled={processing}
                      />
                    ))}
                  </RadioGroup>
                </fieldset>

                <fieldset className="space-y-2">
                  <legend className="mb-2 text-sm font-semibold">Rentang waktu</legend>
                  <RadioGroup
                    className="gap-2!"
                    name="range"
                    aria-label="Rentang waktu"
                    value={range}
                    onValueChange={(next) => setRange(next as ExportRange)}
                    disabled={processing}
                  >
                    <Choice id="range-all" value="all" selected={range === 'all'} title="Semua data" disabled={processing} />
                    <Choice
                      id="range-custom"
                      value="custom"
                      selected={range === 'custom'}
                      title="Rentang tanggal"
                      hint="Tanggal WIB, termasuk tanggal akhir. Percakapan dihitung dari tanggal dibuat, hasil asesmen dari tanggal selesai."
                      disabled={processing}
                    />
                  </RadioGroup>

                  {range === 'custom' ? (
                    <div className="grid gap-4 pt-2 sm:grid-cols-2">
                      <FormField id="ekspor-from" label="Dari tanggal" error={fieldErrors.from} required>
                        {(control) => (
                          <Input {...control} type="date" name="from" value={from} max={to || today} disabled={processing} onChange={(event) => setFrom(event.target.value)} />
                        )}
                      </FormField>
                      <FormField id="ekspor-to" label="Sampai tanggal" error={fieldErrors.to} required>
                        {(control) => (
                          <Input {...control} type="date" name="to" value={to} min={from || undefined} max={today} disabled={processing} onChange={(event) => setTo(event.target.value)} />
                        )}
                      </FormField>
                    </div>
                  ) : null}
                </fieldset>
              </CardContent>

              <div className="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p aria-live="polite" className="text-[0.8125rem] text-text-secondary">
                  {processing ? 'Menyiapkan berkas. Ini bisa memakan waktu bila datanya banyak.' : 'Berkas dibuat saat itu juga dan tidak disimpan di server.'}
                </p>
                <Button type="submit" disabled={processing} className="rounded-full! px-6 text-sm">
                  {processing ? (
                    <LoaderCircle className="animate-spin motion-reduce:animate-none" aria-hidden="true" />
                  ) : (
                    <Download aria-hidden="true" />
                  )}
                  {processing ? 'Menyiapkan...' : 'Unduh Excel'}
                </Button>
              </div>
            </form>
          </Card>
        </div>
      </div>
    </AdminShell>
  );
}
