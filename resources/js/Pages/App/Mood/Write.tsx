import { useCallback, useEffect, useId, useMemo, useRef, useState, type FormEvent } from 'react';
import { router, useForm } from '@inertiajs/react';
import { CircleAlert, Info, Moon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import MoodPicker from '@/Components/shared/MoodPicker';
import WritingPaper from '@/Components/shared/WritingPaper';
import QuietRoom from '@/Components/shared/QuietRoom';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { AROUSAL_LABELS, MOOD_LABELS, moodImage } from '@/lib/mood';
import type { ArousalValue, JournalEntryData, MoodValue } from '@/lib/mood';
import { writingStamp } from '@/lib/writing';

type WriteProps = {
  limit: number;
  remaining: number;
  note_max_chars: number;
  initial_mood: MoodValue | null;
  initial_arousal: ArousalValue | null;
  /** Catatan yang diubah (mode ubah, TASK-053); `null` berarti menulis catatan baru. */
  editing: JournalEntryData | null;
};

type FormData = {
  mood: MoodValue | null;
  arousal_input: ArousalValue | null;
  note: string;
};

type PendingVisit = {
  url: string;
  method: 'get' | 'post' | 'put' | 'patch' | 'delete';
};

const PLACEHOLDER = 'Tulis apa saja yang ingin kamu ingat dari hari ini...';

/** SCR-029. Ruang tulis: kertas bergaris yang menulis seperti mesin tik, dengan mode Ruang sunyi. */
export default function Write({ limit, remaining, note_max_chars, initial_mood, initial_arousal, editing }: WriteProps) {
  const form = useForm<FormData>({
    mood: editing?.mood ?? initial_mood,
    arousal_input: editing ? editing.arousal_input : initial_arousal,
    note: editing?.note ?? '',
  });
  const errors = form.errors as Partial<Record<string, string>>;

  const [quiet, setQuiet] = useState(false);
  const [pending, setPending] = useState<PendingVisit | null>(null);
  const allowLeave = useRef(false);
  const counterId = useId();
  const quietCounterId = `${counterId}-quiet`;
  const stamp = useMemo(() => writingStamp(editing ? new Date(editing.logged_at) : new Date()), [editing]);
  const backHref = editing ? '/app/mood/buku' : '/app/mood';

  const hasNote = form.data.note.trim().length > 0;
  const limitReached = remaining <= 0;
  const canSubmit = form.data.mood !== null && !form.processing;
  // Ada yang belum tersimpan: catatan baru yang sudah ditulis, atau catatan lama yang berubah.
  const dirty = editing ? form.data.note !== editing.note : hasNote;
  const hasProgress = dirty && !allowLeave.current;

  // Peringatan sebelum keluar lewat navigasi di dalam aplikasi.
  useEffect(() => {
    return router.on('before', (event) => {
      if (allowLeave.current || !dirty) {
        return;
      }
      const visit = event.detail.visit;
      setPending({ url: visit.url.href, method: visit.method });
      return false;
    });
  }, [dirty]);

  // Peringatan bawaan browser saat tab ditutup atau dimuat ulang.
  useEffect(() => {
    if (!hasProgress) {
      return;
    }
    const handler = (event: BeforeUnloadEvent) => {
      event.preventDefault();
    };
    window.addEventListener('beforeunload', handler);
    return () => window.removeEventListener('beforeunload', handler);
  }, [hasProgress]);

  const submit = (event?: FormEvent) => {
    event?.preventDefault();
    if (!canSubmit) {
      return;
    }
    allowLeave.current = true;
    const options = {
      onError: () => {
        allowLeave.current = false;
        setQuiet(false);
      },
    };
    if (editing) {
      form.patch(`/app/mood/${editing.id}`, options);
    } else {
      form.post('/app/mood', options);
    }
  };

  const leave = () => {
    if (!pending) {
      return;
    }
    allowLeave.current = true;
    const target = pending;
    setPending(null);
    router.visit(target.url, { method: target.method });
  };

  const closeQuiet = useCallback(() => setQuiet(false), []);

  const paper = (inQuietRoom: boolean) => (
    <WritingPaper
      value={form.data.note}
      onChange={(value) => form.setData('note', value)}
      maxLength={note_max_chars}
      ariaLabel="Catatan"
      placeholder={PLACEHOLDER}
      disabled={form.processing}
      autoFocus={inQuietRoom}
      invalid={Boolean(errors.note)}
      describedBy={inQuietRoom ? quietCounterId : counterId}
      quiet={inQuietRoom}
      onSubmitShortcut={submit}
      className={inQuietRoom ? 'min-h-0 flex-1' : 'h-[min(34rem,60dvh)]'}
    />
  );

  const counter = (inQuietRoom: boolean) => (
    <p id={inQuietRoom ? quietCounterId : counterId} className={inQuietRoom ? 'text-[0.8125rem] text-(--room-ink-soft)' : 'text-[0.8125rem] text-text-secondary'}>
      {form.data.note.length}/{note_max_chars}
    </p>
  );

  const saveLabel = form.processing
    ? 'Memproses...'
    : editing
      ? 'Simpan perubahan'
      : hasNote
        ? 'Simpan catatan'
        : 'Simpan tanpa catatan';
  // Mengubah selalu butuh catatan terisi (mengosongkannya = menghapus, lewat Buku harian).
  const submitReady = canSubmit && (!editing || hasNote);

  return (
    <AppShell title={editing ? 'Ubah catatan' : 'Ruang tulis'} staggered>
      <PageHeader
        title={editing ? 'Ubah catatan' : 'Ruang tulis'}
        description={
          editing
            ? 'Ubah catatanmu, perasaan, atau tenaganya. Waktu dan tanggal catatan tetap.'
            : 'Tulis apa saja yang ingin kamu ingat dari hari ini. Tidak ada yang salah dengan apa pun yang kamu tulis.'
        }
        back={editing ? { href: backHref, label: 'Buku harian' } : { href: backHref, label: 'Mood Tracker' }}
      />

      {limitReached ? (
        <Alert data-load>
          <Info aria-hidden="true" />
          <AlertDescription>Kamu sudah mencatat {limit} kali hari ini. Besok kamu bisa mencatat lagi.</AlertDescription>
        </Alert>
      ) : (
        <form onSubmit={submit} noValidate className="flex flex-col gap-4">
          <Card data-load role="region" aria-label="Perasaanmu" className="gap-4 p-5">
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
            </CardContent>
          </Card>

          <Card data-load role="region" aria-label="Catatan" className="gap-4 p-5">
            <CardContent className="flex flex-col gap-3">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="min-w-0">
                  <p className="text-sm font-semibold text-text">{stamp.date}</p>
                  <p className="text-[0.8125rem] text-text-secondary">
                    {stamp.time} WIB
                    {form.data.arousal_input ? ` · ${AROUSAL_LABELS[form.data.arousal_input]}` : ''}
                  </p>
                </div>
                <div className="flex items-center gap-3">
                  {form.data.mood ? (
                    <img
                      src={moodImage(form.data.mood)}
                      width={48}
                      height={48}
                      alt={MOOD_LABELS[form.data.mood]}
                      className="size-12 object-contain"
                    />
                  ) : null}
                  <Button type="button" variant="outline" className="rounded-full! text-sm" onClick={() => setQuiet(true)}>
                    <Moon aria-hidden="true" />
                    Ruang sunyi
                  </Button>
                </div>
              </div>

              {quiet ? (
                <p className="rounded-2xl bg-soft-lavender px-4 py-6 text-center text-sm text-text">
                  Kamu sedang di Ruang sunyi.
                </p>
              ) : (
                paper(false)
              )}

              {errors.note ? (
                <p role="alert" className="flex items-center gap-1.5 text-sm text-destructive">
                  <CircleAlert className="size-4 shrink-0" aria-hidden="true" />
                  {errors.note}
                </p>
              ) : null}
              {errors.arousal_input ? (
                <p role="alert" className="flex items-center gap-1.5 text-sm text-destructive">
                  <CircleAlert className="size-4 shrink-0" aria-hidden="true" />
                  {errors.arousal_input}
                </p>
              ) : null}

              <div className="flex flex-wrap items-center justify-between gap-3">
                {counter(false)}
                <p className="text-[0.8125rem] text-text-secondary">Ctrl + Enter untuk menyimpan</p>
              </div>
            </CardContent>
          </Card>

          <Button type="submit" className="w-full rounded-full! text-sm sm:w-auto sm:self-end" disabled={!submitReady}>
            {saveLabel}
          </Button>
          {form.data.mood === null ? (
            <p className="text-center text-[0.8125rem] text-text-secondary sm:text-right">Pilih perasaanmu dulu, ya.</p>
          ) : null}
        </form>
      )}

      <QuietRoom
        open={quiet}
        onClose={closeQuiet}
        header={
          <span className="flex items-center gap-3">
            {form.data.mood ? (
              <img src={moodImage(form.data.mood)} width={32} height={32} alt={MOOD_LABELS[form.data.mood]} className="size-8 object-contain" />
            ) : null}
            <span>
              {stamp.date}, {stamp.time} WIB
            </span>
          </span>
        }
        footer={
          <div className="flex items-center justify-between gap-3">
            {counter(true)}
            <Button type="button" className="quiet-button rounded-full! text-sm" disabled={!submitReady} onClick={() => submit()}>
              {saveLabel}
            </Button>
          </div>
        }
      >
        {paper(true)}
      </QuietRoom>

      <ConfirmLeaveDialog
        open={pending !== null}
        title={editing ? 'Keluar tanpa menyimpan?' : 'Keluar dari Ruang tulis?'}
        description={editing ? 'Perubahanmu belum tersimpan. Yakin ingin keluar?' : 'Catatanmu belum tersimpan. Yakin ingin keluar?'}
        stayLabel="Lanjut menulis"
        leaveLabel="Keluar"
        onStay={() => setPending(null)}
        onLeave={leave}
      />
    </AppShell>
  );
}
