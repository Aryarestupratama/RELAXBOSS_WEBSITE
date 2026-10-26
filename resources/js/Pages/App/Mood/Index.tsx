import { useEffect, useRef, useState, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { CircleAlert, CircleCheck, Info } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import FormField from '@/Components/shared/FormField';
import MoodPicker from '@/Components/shared/MoodPicker';
import { MoodDayChart, MoodRangeChart } from '@/Components/shared/MoodChart';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
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

const cardClass = 'rounded-3xl bg-card p-5 shadow-lift';
const pillButton = 'rounded-full! px-5 text-sm';

function EntryList({ entries }: { entries: MoodEntryItem[] }) {
  return (
    <ul className="grid gap-2">
      {entries.map((entry) => (
        <li key={entry.id} className="rounded-2xl bg-neutral-soft px-3.5 py-2.5 text-sm">
          <p className="font-semibold">
            {entry.time} WIB <span aria-hidden="true">·</span> {entry.mood_label}
            {entry.arousal_label ? <span className="font-normal text-text-secondary"> · {entry.arousal_label}</span> : null}
          </p>
          {entry.note ? <p className="mt-0.5 whitespace-pre-wrap break-words text-[0.8125rem] text-text-secondary">{entry.note}</p> : null}
        </li>
      ))}
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
                <span className="sr-only">
                  {day.label}: Tidak ada catatan
                </span>
              </span>
            ) : (
              <button
                type="button"
                onClick={() => onSelectDay(day.date)}
                aria-pressed={selectedDate === day.date}
                aria-label={`${day.label}: ${MOOD_LABELS[level]}, ${day.entries.length} catatan`}
                className={`grid size-11 place-items-center rounded-full transition-transform motion-safe:hover:scale-110 ${
                  selectedDate === day.date ? 'ring-2 ring-brand ring-offset-2 ring-offset-neutral-soft' : ''
                }`}
              >
                <img src={moodImage(level)} width={48} height={48} alt="" className="size-9 object-contain sm:size-10" />
              </button>
            )}
            <span className={`text-xs ${day.is_today ? 'font-semibold text-text' : 'text-text-secondary'}`}>
              {weekday}
            </span>
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

  const [range, setRange] = useState<7 | 30>(7);
  const [selectedDate, setSelectedDate] = useState<string | null>(null);
  const dayHeading = useRef<HTMLHeadingElement | null>(null);

  const selectedDay = days.find((day) => day.date === selectedDate) ?? null;
  const visibleDays = days.slice(-range);
  const hasData = visibleDays.some((day) => day.entries.length > 0);
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

  return (
    <AppShell title="Mood Tracker" wide staggered>
      <div data-load>
        <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">Mood Tracker</h1>
        <p className="mt-1 text-sm text-text-secondary">Catat perasaanmu dan lihat polanya dalam seminggu.</p>
      </div>

      {saved ? (
        <Alert data-load className="mt-3">
          <CircleCheck className="text-success!" aria-hidden="true" />
          <AlertDescription>Catatanmu tersimpan.</AlertDescription>
        </Alert>
      ) : null}

      <div className="mt-4 grid gap-4 lg:grid-cols-7">
        <section aria-labelledby="mood-form-title" data-load className={`${cardClass} lg:col-span-4`}>
            <h2 id="mood-form-title" className="text-base font-semibold">
              Catat mood
            </h2>

            {limitReached ? (
              <div className="mt-3">
                <Alert>
                  <Info aria-hidden="true" />
                  <AlertDescription>
                    Kamu sudah mencatat {limit} kali hari ini. Besok kamu bisa mencatat lagi.
                  </AlertDescription>
                </Alert>
              </div>
            ) : (
              <form onSubmit={submit} noValidate className="mt-2 space-y-4">
                <p className="text-[0.8125rem] text-text-secondary">
                  Kamu bisa mencatat {remaining} kali lagi hari ini. Boleh lebih dari sekali, kalau perasaanmu berubah.
                </p>

                <div>
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
                </div>

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
                      className="min-h-20! text-sm"
                      onChange={(event) => form.setData('note', event.target.value)}
                    />
                  )}
                </FormField>

                <Button type="submit" className="w-full rounded-full! text-sm" disabled={form.processing}>
                  {form.processing ? 'Memproses...' : 'Catat mood'}
                </Button>
              </form>
            )}
        </section>

        <section aria-labelledby="mood-today-title" data-load className={`${cardClass} lg:col-span-3`}>
          <h2 id="mood-today-title" className="text-base font-semibold">
            Hari ini
          </h2>
          <div className="mt-3">
            {today.length === 0 ? (
              <p className="text-sm text-text-secondary">Belum ada catatan hari ini.</p>
            ) : (
              <EntryList entries={today} />
            )}
          </div>
        </section>

        <section aria-labelledby="mood-chart-title" data-load className={`${cardClass} lg:col-span-7`}>
            <div className="flex flex-wrap items-center justify-between gap-3">
              <h2 id="mood-chart-title" className="text-base font-semibold">
                Polamu
              </h2>
              <div className="flex flex-wrap items-center gap-2">
                {daysWithEntries.length > 0 ? (
                  <>
                    <label htmlFor="mood-day-select" className="sr-only">
                      Lihat catatan satu hari
                    </label>
                    <select
                      id="mood-day-select"
                      value={selectedDate ?? ''}
                      onChange={(event) => setSelectedDate(event.target.value === '' ? null : event.target.value)}
                      className="min-h-9 rounded-full border-[1.5px] border-input bg-card px-3 text-sm text-foreground"
                    >
                      <option value="">Lihat catatan satu hari</option>
                      {daysWithEntries.map((day) => (
                        <option key={day.date} value={day.date}>
                          {day.label} ({day.entries.length} catatan)
                        </option>
                      ))}
                    </select>
                  </>
                ) : null}
                <div role="group" aria-label="Rentang grafik" className="flex gap-1.5">
                  {([7, 30] as const).map((value) => (
                    <Button
                      key={value}
                      type="button"
                      variant={range === value ? 'default' : 'outline'}
                      className={pillButton}
                      aria-pressed={range === value}
                      onClick={() => setRange(value)}
                    >
                      {value} hari
                    </Button>
                  ))}
                </div>
              </div>
            </div>

            {hasData ? (
              <div className="mt-3 space-y-3">
                {range === 7 ? (
                  <WeekFaces days={visibleDays} selectedDate={selectedDate} onSelectDay={setSelectedDate} />
                ) : null}
                <MoodRangeChart days={days} range={range} selectedDate={selectedDate} onSelectDay={setSelectedDate} />
              </div>
            ) : (
              <div className="mt-3 flex flex-col items-center rounded-2xl bg-soft-lavender px-4 py-6 text-center">
                <img src="/images/mood/mood-row.webp" width={240} height={40} alt="" className="h-auto w-full max-w-52" />
                <p className="mt-3 max-w-sm text-sm text-text">
                  {range === 7
                    ? 'Belum ada catatan minggu ini. Bagaimana perasaanmu hari ini?'
                    : 'Belum ada catatan dalam 30 hari terakhir. Bagaimana perasaanmu hari ini?'}
                </p>
              </div>
            )}

          {selectedDay ? (
            <div className="mt-4 space-y-3 border-t border-border pt-4">
              <h3 ref={dayHeading} tabIndex={-1} className="text-[0.9375rem] font-semibold outline-none">
                Catatan {selectedDay.label}
              </h3>
              <MoodDayChart label={selectedDay.label} entries={selectedDay.entries} />
              <EntryList entries={selectedDay.entries} />
              <Button type="button" variant="outline" className={pillButton} onClick={() => setSelectedDate(null)}>
                Tutup catatan hari
              </Button>
            </div>
          ) : null}
        </section>
      </div>
    </AppShell>
  );
}
