import { type FormEvent, type ReactNode } from 'react';
import { router, useForm } from '@inertiajs/react';
import { CircleAlert, Info, PenLine } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import MoodPicker from '@/Components/shared/MoodPicker';
import { MoodRangeChart } from '@/Components/shared/MoodChart';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { moodImage, moodLevel, MOOD_LABELS, weekdayShort } from '@/lib/mood';
import type { ArousalValue, MoodDay, MoodEntryItem, MoodValue } from '@/lib/mood';

type IndexProps = {
  today: MoodEntryItem[];
  limit: number;
  remaining: number;
  days: MoodDay[];
};

type FormData = {
  mood: MoodValue | null;
  arousal_input: ArousalValue | null;
};

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
              <span role="img" aria-label={`${day.label}: ${MOOD_LABELS[level]}, ${day.entries.length} catatan`} className="grid size-9 place-items-center sm:size-11">
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

/** Satu bagian rentang di kartu Polamu (7 dan 30 hari tampil bersamaan, tanpa tab). */
function RangeSection({ id, title, children }: { id: string; title: string; children: ReactNode }) {
  return (
    <section aria-labelledby={id} className="space-y-3">
      <h3 id={id} className="text-sm font-semibold text-text">
        {title}
      </h3>
      {children}
    </section>
  );
}

/** SCR-016 */
export default function Index({ limit, remaining, days }: IndexProps) {
  const form = useForm<FormData>({ mood: null, arousal_input: null });
  const errors = form.errors as Partial<Record<string, string>>;

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/app/mood', {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
  };

  /** Membawa pilihan mood dan tenaga ke Ruang tulis supaya tidak perlu memilih lagi. */
  const openWritingRoom = () => {
    router.get('/app/mood/tulis', {
      mood: form.data.mood ?? undefined,
      arousal_input: form.data.arousal_input ?? undefined,
    });
  };

  const limitReached = remaining <= 0;

  const weekDays = days.slice(-7);
  const weekHasData = weekDays.some((day) => day.entries.length > 0);
  const monthHasData = days.some((day) => day.entries.length > 0);

  return (
    <AppShell title="Mood Tracker" wide staggered>
      <PageHeader title="Mood Tracker" description="Catat perasaanmu dan lihat polanya dalam seminggu dan sebulan." />

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">
        <div className="flex flex-col lg:col-span-5">
          <Card data-load role="region" aria-labelledby="mood-form-title" className={cardPadding}>
            <CardHeader>
              <CardTitle id="mood-form-title" role="heading" aria-level={2} className="text-base">
                Catat mood
              </CardTitle>
              {limitReached ? null : (
                <CardDescription className="text-[0.8125rem]">
                  Kamu bisa mencatat {remaining} kali lagi hari ini. Boleh lebih dari sekali, kalau perasaanmu berubah. Mau menuliskannya? Buka Ruang tulis.
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

                <CardFooter className="flex-col gap-2">
                  <Button type="submit" className="w-full rounded-full! text-sm" disabled={form.processing}>
                    {form.processing ? 'Memproses...' : 'Catat mood'}
                  </Button>
                  <Button type="button" variant="outline" className="w-full rounded-full! text-sm" onClick={openWritingRoom} disabled={form.processing}>
                    <PenLine aria-hidden="true" />
                    Tulis di Ruang tulis
                  </Button>
                </CardFooter>
              </form>
            )}
          </Card>
        </div>

        <div className="flex flex-col lg:col-span-7">
          <Card data-load role="region" aria-labelledby="mood-chart-title" className={cardPadding}>
            <CardHeader>
              <CardTitle id="mood-chart-title" role="heading" aria-level={2} className="text-base">
                Polamu
              </CardTitle>
              <CardDescription className="text-[0.8125rem]">Satu titik per hari, dari rata-rata catatanmu.</CardDescription>
            </CardHeader>

            {monthHasData ? (
              <CardContent className="flex flex-col gap-6">
                <RangeSection id="mood-range-7" title="7 hari terakhir">
                  {weekHasData ? (
                    <>
                      <WeekFaces days={weekDays} />
                      <MoodRangeChart days={days} range={7} height={180} />
                    </>
                  ) : (
                    <p className="rounded-2xl bg-soft-lavender px-4 py-3 text-sm text-text">
                      Belum ada catatan minggu ini. Bagaimana perasaanmu hari ini?
                    </p>
                  )}
                </RangeSection>
                <RangeSection id="mood-range-30" title="30 hari terakhir">
                  <MoodRangeChart days={days} range={30} showCaption={false} height={150} />
                </RangeSection>
              </CardContent>
            ) : (
              <CardContent>
                <div className="flex flex-col items-center rounded-2xl bg-soft-lavender px-4 py-6 text-center">
                  <img src="/images/mood/mood-row.webp" width={240} height={40} alt="" className="h-auto w-full max-w-52" />
                  <p className="mt-3 max-w-sm text-sm text-text">
                    Belum ada catatan dalam 30 hari terakhir. Bagaimana perasaanmu hari ini?
                  </p>
                </div>
              </CardContent>
            )}
          </Card>
        </div>
      </div>
    </AppShell>
  );
}
