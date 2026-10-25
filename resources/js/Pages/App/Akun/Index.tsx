import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Check, CircleAlert, CircleCheck, Info, Trash2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import DeleteAccountDialog from '@/Components/shared/DeleteAccountDialog';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import {
  CONSENT_ERROR,
  CONSENT_TRAINING_QUESTION,
  CONSENT_TRAINING_TEXT,
  type SharedConsentProps,
} from '@/lib/consent';

type IndexProps = {
  profile: {
    name: string;
    email: string;
    major: string | null;
    institution_name: string | null;
  };
  is_only_admin: boolean;
};

type Choice = 'granted' | 'denied';

const cardClass = 'rounded-3xl bg-card p-5 shadow-lift';

const CHOICES: { value: Choice; label: string }[] = [
  { value: 'granted', label: 'Ya, boleh' },
  { value: 'denied', label: 'Tidak, terima kasih' },
];

function statusText(choice: Choice | null, answered: boolean): string {
  if (choice === null) {
    return 'Kamu belum memilih.';
  }
  if (!answered) {
    return 'Teks persetujuan sudah diperbarui. Pilih lagi, ya.';
  }
  return choice === 'granted' ? 'Pilihanmu saat ini: Ya, boleh.' : 'Pilihanmu saat ini: Tidak, terima kasih.';
}

/** SCR-018 */
export default function Index({ profile, is_only_admin }: IndexProps) {
  const { consent } = usePage<SharedConsentProps>().props;
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [failed, setFailed] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);

  const choice = consent?.training_choice ?? null;
  const answered = consent?.training_answered ?? false;

  const saveChoice = (value: Choice) => {
    setSaved(false);
    setFailed(false);
    router.post(
      '/app/akun/persetujuan-pelatihan',
      { choice: value },
      {
        preserveScroll: true,
        onStart: () => setSaving(true),
        onSuccess: () => setSaved(true),
        onError: () => setFailed(true),
        onFinish: () => setSaving(false),
      },
    );
  };

  const initial = profile.name.trim().charAt(0).toUpperCase() || '?';

  return (
    <AppShell title="Akun" wide staggered>
      <div data-load>
        <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">Akun</h1>
        <p className="mt-1 text-sm text-text-secondary">Data akunmu dan pengaturan privasi.</p>
      </div>

      <div className="mt-4">
        <div className="min-w-0 space-y-4">
          <section
                        aria-labelledby="judul-info-akun"
            data-load
            className="overflow-hidden rounded-3xl bg-card shadow-lift"
          >
            <div className="flex items-center gap-4 bg-soft-lavender px-5 py-4" aria-hidden="true">
              <span className="grid size-12 shrink-0 select-none place-items-center rounded-full bg-card/60 text-xl font-bold text-text">
                {initial}
              </span>
              <div className="min-w-0">
                <p className="truncate text-base font-bold text-text">{profile.name}</p>
                <p className="truncate text-[0.8125rem] text-text-secondary">{profile.email}</p>
              </div>
            </div>

            <div className="p-5">
              <h2 id="judul-info-akun" className="text-base font-semibold">
                Info akun
              </h2>
              <dl className="mt-3 grid gap-3 sm:grid-cols-2">
                {[
                  { label: 'Nama', value: profile.name, breakAll: false },
                  { label: 'Email', value: profile.email, breakAll: true },
                  { label: 'Jurusan', value: profile.major, breakAll: false },
                  { label: 'Kampus', value: profile.institution_name, breakAll: false },
                ].map(({ label, value, breakAll }) => (
                  <div key={label} className="min-w-0">
                    <dt className="mb-1 text-xs text-text-secondary">{label}</dt>
                    <dd
                      className={`rounded-xl bg-neutral-soft px-3.5 py-2 text-sm ${breakAll ? 'break-all' : 'break-words'} ${
                        value ? '' : 'text-text-secondary italic'
                      }`}
                    >
                      {value ?? 'Belum diisi'}
                    </dd>
                  </div>
                ))}
              </dl>
            </div>
          </section>

          <div className="grid items-start gap-4 xl:grid-cols-5">
            <section
                            aria-labelledby="judul-persetujuan"
              data-load
              className={`${cardClass} xl:col-span-3`}
            >
              <h2 id="judul-persetujuan" className="text-base font-semibold">
                Persetujuan pelatihan
              </h2>
              <p className="mt-2 text-sm font-semibold">{CONSENT_TRAINING_QUESTION}</p>
              <p className="mt-1 text-[0.8125rem] leading-relaxed text-text-secondary">{CONSENT_TRAINING_TEXT}</p>

              <p className="mt-4 flex items-center gap-2 rounded-xl bg-neutral-soft px-3.5 py-2 text-sm font-medium" role="status">
                {answered && choice !== null ? (
                  <CircleCheck className="size-4.5 shrink-0 text-success" aria-hidden="true" />
                ) : (
                  <Info className="size-4.5 shrink-0 text-text-secondary" aria-hidden="true" />
                )}
                {statusText(choice, answered)}
              </p>

              {saved ? (
                <Alert className="mt-3">
                  <CircleCheck className="text-success!" aria-hidden="true" />
                  <AlertDescription>Pilihanmu tersimpan.</AlertDescription>
                </Alert>
              ) : null}
              {failed ? (
                <Alert variant="destructive" className="mt-3">
                  <CircleAlert aria-hidden="true" />
                  <AlertDescription>{CONSENT_ERROR}</AlertDescription>
                </Alert>
              ) : null}

              <div className="mt-3 flex flex-col gap-2 sm:flex-row">
                {CHOICES.map(({ value, label }) => {
                  const selected = answered && choice === value;
                  return (
                    <Button
                      key={value}
                      type="button"
                      variant="outline"
                      aria-pressed={selected}
                      disabled={saving}
                      onClick={() => saveChoice(value)}
                      className={`rounded-full! px-5 text-sm ${
                        selected ? 'border-2 border-primary bg-soft-lavender font-semibold hover:bg-soft-lavender hover:text-primary' : ''
                      }`}
                    >
                      {selected ? <Check aria-hidden="true" /> : null}
                      {label}
                    </Button>
                  );
                })}
              </div>
            </section>

            <section
                            aria-labelledby="judul-hapus-akun"
              data-load
              className="rounded-3xl bg-crisis-bg p-5 shadow-lift xl:col-span-2"
            >
              <h2 id="judul-hapus-akun" className="flex items-center gap-2 text-base font-semibold text-crisis-text">
                <Trash2 className="size-4.5" aria-hidden="true" />
                Hapus akun
              </h2>
              <p className="mt-2 text-[0.8125rem] leading-relaxed text-text-secondary">
                Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Data yang sudah diekspor untuk
                pelatihan model (bila kamu menyetujuinya) tidak ikut terhapus, dan salinan cadangan hilang otomatis
                sesuai masa simpannya.
              </p>

              {is_only_admin ? (
                <Alert className="mt-3">
                  <Info aria-hidden="true" />
                  <AlertDescription>
                    Kamu satu-satunya admin, jadi akun ini belum bisa dihapus. Jadikan orang lain admin lebih dulu.
                  </AlertDescription>
                </Alert>
              ) : null}

              <div className="mt-4">
                <Button
                  type="button"
                  variant="destructive"
                  className="rounded-full! px-5 text-sm"
                  disabled={is_only_admin}
                  onClick={() => setDeleteOpen(true)}
                >
                  Hapus akun
                </Button>
              </div>
            </section>
          </div>
        </div>
      </div>

      <DeleteAccountDialog open={deleteOpen} onClose={() => setDeleteOpen(false)} />
    </AppShell>
  );
}
