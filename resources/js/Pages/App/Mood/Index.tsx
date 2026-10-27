import { useEffect, useState, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { CircleAlert, Info } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import FormField from '@/Components/shared/FormField';
import MoodPicker from '@/Components/shared/MoodPicker';
import { MoodRangeChart } from '@/Components/shared/MoodChart';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Textarea } from '@/Components/ui/textarea';
import { moodImage, moodLevel, MOOD_LABELS, weekdayShort } from '@/lib/mood';
import type { ArousalValue, MoodDay, MoodEntryItem, MoodValue } from '@/lib/mood';

type IndexProps = {
  today: MoodEntryItem[];
  limit: number;
  remaining: number;
  days: MoodDay[];
  note_max_chars: number;
};

type FormData = {
  mood: MoodValue | null;
  arousal_input: ArousalValue | null;
  note: string;
};

type Range = 7 | 30;

const cardPadding = 'gap-4 p-5';

/** Satu wajah per hari untuk 7 hari terakhir. Hari tanpa catatan menjadi lingkaran putus-putus (celah, bukan nol). */
function WeekFaces({ days }: { days: MoodDay[] }) {
  return (
    <ul className="grid grid-cols-7 gap-1 rounded-2xl border border-border/60 bg-card px-1 py-2.5 text-center sm:gap-2 sm:px-2">
      {days.map((day) => {
        const weekday = weekdayShort(day.date);
        const level = day.average === null ? null : moodLevel(day.average);
        return (
          <li key={day.date} className="flex flex-col items-center gap-1">
            {level === null ? (
              <span className="grid size-9 place-items-center rounded-full border-2 border-dashed border-border sm:size-11">
                <span className="sr-only">{day.label}: Tidak ada catatan</span>
              </span>
            ) : (
              <span role="img" aria-label={`${day.label}: ${MOOD_LABELS[level]}, ${day.entries.length} catatan`} className="grid size-11 place-items-center">
                <img src={moodImage(level)} width={48} height={48} alt="" className="size-9 object-contain sm:size-10" />
              </span>
            )}
            <span className={`text-xs ${day.is_today ? 'font-semibold text-text' : 'text-text-secondary'}`}>{weekday}</span>
          </li>
        );
      })}
    </ul>
  );
}

/** SCR-016 */
export default function Index({ limit, remaining, days, note_max_chars }: IndexProps) {
  const form = useForm<FormData>({ mood: null, arousal_input: null, note: '' });
  const errors = form.errors as Partial<Record<string, string>>;

  const [range, setRange] = useState<Range>(7);

  useEffect(() => {
    if (errors.note) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [errors.note]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/app/mood', {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
  };

  const limitReached = remaining <= 0;

  const renderRange = (value: Range) => {
    const visibleDays = days.slice(-value);
    const hasData = visibleDays.some((day) => day.entries.length > 0);

    if (!hasData) {
      return (
        <div className="flex flex-col items-center rounded-2xl bg-soft-lavender px-4 py-6 text-center">
          <img src="/images/mood/mood-row.webp" width={240} height={40} alt="" className="h-auto w-full max-w-52" />
          <p className="mt-3 max-w-sm text-sm text-text">
            {value === 7
              ? 'Belum ada catatan minggu ini. Bagaimana perasaanmu hari ini?'
              : 'Belum ada catatan dalam 30 hari terakhir. Bagaimana perasaanmu hari ini?'}
          </p>
        </div>
      );
    }

    return (
      <div className="space-y-3">
        {value === 7 ? <WeekFaces days={visibleDays} /> : null}
        <MoodRangeChart days={days} range={value} />
      </div>
    );
  };

  return (
    <AppShell title="Mood Tracker" wide staggered>
      <PageHeader title="Mood Tracker" description="Catat perasaanmu dan lihat polanya dalam seminggu." />

      <div className="grid gap-4 lg:grid-cols-12 lg:items-stretch">
        <div className="flex flex-col lg:col-span-5">
          <Card data-load role="region" aria-labelledby="mood-form-title" className={`${cardPadding} lg:h-full`}>
            <CardHeader>
              <CardTitle id="mood-form-title" role="heading" aria-level={2} className="text-base">
                Catat mood
              </CardTitle>
              {limitReached ? null : (
                <CardDescription className="text-[0.8125rem]">
                  Kamu bisa mencatat {remaining} kali lagi hari ini. Boleh lebih dari sekali, kalau perasaanmu berubah.
                </CardDescription>
              )}
            </CardHeader>
            <Separator />

            {limitReached ? (
              <CardContent>
                <Alert>
                  <Info aria-hidden="true" />
                  <AlertDescription>Kamu sudah mencatat {limit} kali hari ini. Besok kamu bisa mencatat lagi.</AlertDescription>
                </Alert>
              </CardContent>
            ) : (
              <form onSubmit={submit} noValidate className="flex flex-col gap-5">
                <CardContent>
                  <MoodPicker
                    mood={form.data.mood}
                    arousal={form.data.arousal_input}
                    onMoodChange={(value) => form.setData('mood', value)}
                    onArousalChange={(value) => form.setData('arousal_input', value)}
                    disabled={form.processing}
                    invalid={Boolean(errors.mood)}
                    errorId={errors.mood ? 'mood-error' : undefined}
                  />
                  {errors.mood ? (
                    <p id="mood-error" role="alert" className="mt-2 flex items-center gap-1.5 text-sm text-destructive">
                      <CircleAlert className="size-4 shrink-0" aria-hidden="true" />
                      {errors.mood}
                    </p>
                  ) : null}
                  {errors.arousal_input ? (
                    <p role="alert" className="mt-2 flex items-center gap-1.5 text-sm text-destructive">
                      <CircleAlert className="size-4 shrink-0" aria-hidden="true" />
                      {errors.arousal_input}
                    </p>
                  ) : null}
                </CardContent>

                <CardContent>
                  <FormField
                    id="mood-note"
                    label="Catatan (boleh dikosongkan)"
                    error={errors.note}
                    hint={`${form.data.note.length}/${note_max_chars}`}
                    compact
                  >
                    {(control) => (
                      <Textarea
                        {...control}
                        name="note"
                        maxLength={note_max_chars}
                        value={form.data.note}
                        className="min-h-20! rounded-xl! text-sm"
                        onChange={(event) => form.setData('note', event.target.value)}
                      />
                    )}
                  </FormField>
                </CardContent>

                <CardFooter>
                  <Button type="submit" className="w-full rounded-full! text-sm" disabled={form.processing}>
                    {form.processing ? 'Memproses...' : 'Catat mood'}
                  </Button>
                </CardFooter>
              </form>
            )}
          </Card>
        </div>

        <div className="flex flex-col lg:col-span-7">
          <Card data-load role="region" aria-labelledby="mood-chart-title" className={`${cardPadding} lg:h-full`}>
            <Tabs value={String(range)} onValueChange={(value) => setRange(value === '30' ? 30 : 7)} className="gap-4">
              <CardHeader className="flex-row flex-wrap items-center justify-between gap-3">
                <div>
                  <CardTitle id="mood-chart-title" role="heading" aria-level={2} className="text-base">
                    Polamu
                  </CardTitle>
                  <CardDescription className="mt-1 text-[0.8125rem]">Satu titik per hari, dari rata-rata catatanmu.</CardDescription>
                </div>
                <TabsList aria-label="Rentang grafik">
                  <TabsTrigger value="7">7 hari</TabsTrigger>
                  <TabsTrigger value="30">30 hari</TabsTrigger>
                </TabsList>
              </CardHeader>
              <TabsContent value="7">{renderRange(7)}</TabsContent>
              <TabsContent value="30">{renderRange(30)}</TabsContent>
            </Tabs>
          </Card>
        </div>
      </div>
    </AppShell>
  );
}
