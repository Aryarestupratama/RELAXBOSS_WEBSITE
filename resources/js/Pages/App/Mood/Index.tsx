import { useEffect, useRef, useState, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { BatteryLow, CircleAlert, CircleCheck, Info, Zap, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import FormField from '@/Components/shared/FormField';
import MoodPicker from '@/Components/shared/MoodPicker';
import { MoodDayChart, MoodRangeChart } from '@/Components/shared/MoodChart';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { NativeSelect } from '@/Components/ui/native-select';
import { Progress } from '@/Components/ui/progress';
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
  saved: boolean;
};

type FormData = {
  mood: MoodValue | null;
  arousal_input: ArousalValue | null;
  note: string;
};

type Range = 7 | 30;

const AROUSAL_ICONS: Record<string, LucideIcon> = {
  energized: Zap,
  tired: BatteryLow,
};

const pillButton = 'rounded-full! px-5 text-sm';
const cardPadding = 'gap-4 p-5';

function EntryList({ entries }: { entries: MoodEntryItem[] }) {
  return (
    <ul className="grid gap-2">
      {entries.map((entry) => {
        const ArousalIcon = entry.arousal ? AROUSAL_ICONS[entry.arousal] : undefined;
        return (
          <li key={entry.id} className="rounded-2xl bg-neutral-soft px-3.5 py-3 text-sm">
            <div className="flex flex-wrap items-center gap-x-2.5 gap-y-1.5">
              <img src={moodImage(entry.mood)} width={32} height={32} alt="" className="size-8 shrink-0 object-contain" />
              <span className="font-semibold">{entry.mood_label}</span>
              <Badge variant="outline">{entry.time} WIB</Badge>
              {entry.arousal_label ? (
                <Badge variant="outline">
                  {ArousalIcon ? <ArousalIcon aria-hidden="true" /> : null}
                  {entry.arousal_label}
                </Badge>
              ) : null}
            </div>
            {entry.note ? (
              <p className="mt-2 text-[0.8125rem] leading-relaxed break-words whitespace-pre-wrap text-text-secondary">{entry.note}</p>
            ) : null}
          </li>
        );
      })}
    </ul>
  );
}

/** Satu wajah per hari untuk 7 hari terakhir. Hari tanpa catatan menjadi lingkaran putus-putus (celah, bukan nol). */
function WeekFaces({
  days,
  selectedDate,
  onSelectDay,
}: {
  days: MoodDay[];
  selectedDate: string | null;
  onSelectDay: (date: string) => void;
}) {
  return (
    <ul className="grid grid-cols-7 gap-1 rounded-2xl bg-neutral-soft px-1 py-2.5 text-center sm:gap-2 sm:px-2">
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
              <button
                type="button"
                onClick={() => onSelectDay(day.date)}
                aria-pressed={selectedDate === day.date}
                aria-label={`${day.label}: ${MOOD_LABELS[level]}, ${day.entries.length} catatan`}
                className={`grid size-11 place-items-center rounded-full transition-transform motion-safe:hover:scale-110 ${
                  selectedDate === day.date ? 'bg-card shadow-card ring-2 ring-primary' : ''
                }`}
              >
                <img src={moodImage(level)} width={48} height={48} alt="" className="size-9 object-contain sm:size-10" />
              </button>
            )}
            <span className={`text-xs ${day.is_today ? 'font-semibold text-text' : 'text-text-secondary'}`}>{weekday}</span>
          </li>
        );
      })}
    </ul>
  );
}

/** SCR-016 */
export default function Index({ today, limit, remaining, days, note_max_chars, saved }: IndexProps) {
  const form = useForm<FormData>({ mood: null, arousal_input: null, note: '' });
  const errors = form.errors as Partial<Record<string, string>>;

  const [range, setRange] = useState<Range>(7);
  const [selectedDate, setSelectedDate] = useState<string | null>(null);
  const dayHeading = useRef<HTMLHeadingElement | null>(null);

  const selectedDay = days.find((day) => day.date === selectedDate) ?? null;
  const daysWithEntries = [...days].reverse().filter((day) => day.entries.length > 0);

  useEffect(() => {
    if (errors.note) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [errors.note]);

  useEffect(() => {
    if (selectedDate) {
      dayHeading.current?.focus();
      dayHeading.current?.scrollIntoView({ block: 'nearest' });
    }
  }, [selectedDate]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/app/mood', {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
  };

  const limitReached = remaining <= 0;
  const usedToday = Math.min(limit, Math.max(0, limit - remaining));

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
        {value === 7 ? <WeekFaces days={visibleDays} selectedDate={selectedDate} onSelectDay={setSelectedDate} /> : null}
        <MoodRangeChart days={days} range={value} selectedDate={selectedDate} onSelectDay={setSelectedDate} />
      </div>
    );
  };

  return (
    <AppShell title="Mood Tracker" wide staggered>
      <PageHeader title="Mood Tracker" description="Catat perasaanmu dan lihat polanya dalam seminggu." />

      {saved ? (
        <Alert data-load className="mb-4">
          <CircleCheck className="text-success!" aria-hidden="true" />
          <AlertDescription>Catatanmu tersimpan.</AlertDescription>
        </Alert>
      ) : null}

      <div className="grid items-start gap-4 lg:grid-cols-12">
        <div className="flex flex-col gap-4 lg:col-span-5">
          <Card data-load role="region" aria-labelledby="mood-form-title" className={cardPadding}>
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

          <Card data-load role="region" aria-labelledby="mood-today-title" className={cardPadding}>
            <CardHeader className="flex-row items-center justify-between gap-3">
              <CardTitle id="mood-today-title" role="heading" aria-level={2} className="text-base">
                Hari ini
              </CardTitle>
              <Badge variant="secondary">
                {usedToday} dari {limit} catatan
              </Badge>
            </CardHeader>
            <Progress value={usedToday} max={limit} aria-label={`Catatan hari ini: ${usedToday} dari ${limit}`} />
            <CardContent>
              {today.length === 0 ? (
                <p className="text-sm text-text-secondary">Belum ada catatan hari ini.</p>
              ) : (
                <EntryList entries={today} />
              )}
            </CardContent>
          </Card>
        </div>

        <div className="flex flex-col gap-4 lg:col-span-7">
          <Card data-load role="region" aria-labelledby="mood-chart-title" className={cardPadding}>
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

          {daysWithEntries.length > 0 ? (
            <Card data-load role="region" aria-labelledby="mood-day-title" className={cardPadding}>
              <CardHeader>
                <CardTitle id="mood-day-title" role="heading" aria-level={2} className="text-base">
                  Lihat catatan satu hari
                </CardTitle>
                <CardDescription className="text-[0.8125rem]">Pilih hari, atau sentuh titik di grafik.</CardDescription>
              </CardHeader>
              <div>
                <Label htmlFor="mood-day-select" className="sr-only">
                  Pilih hari
                </Label>
                <NativeSelect
                  id="mood-day-select"
                  variant="pill"
                  value={selectedDate ?? ''}
                  onChange={(event) => setSelectedDate(event.target.value === '' ? null : event.target.value)}
                >
                  <option value="">Pilih hari</option>
                  {daysWithEntries.map((day) => (
                    <option key={day.date} value={day.date}>
                      {day.label} ({day.entries.length} catatan)
                    </option>
                  ))}
                </NativeSelect>
              </div>

              {selectedDay ? (
                <>
                  <Separator />
                  <CardContent className="space-y-3">
                    <h3 ref={dayHeading} tabIndex={-1} className="text-[0.9375rem] font-semibold outline-none">
                      Catatan {selectedDay.label}
                    </h3>
                    <MoodDayChart label={selectedDay.label} entries={selectedDay.entries} />
                    <EntryList entries={selectedDay.entries} />
                  </CardContent>
                  <CardFooter>
                    <Button type="button" variant="outline" className={pillButton} onClick={() => setSelectedDate(null)}>
                      Tutup catatan hari
                    </Button>
                  </CardFooter>
                </>
              ) : null}
            </Card>
          ) : null}
        </div>
      </div>
    </AppShell>
  );
}
