import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Check } from 'lucide-react';
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

const cardClass = 'rounded-xl border border-border bg-card p-5 shadow-card';

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

  return (
    <AppShell title="Akun">
      <h1>Akun</h1>
      <p className="mt-2 text-text-secondary">Data akunmu dan pengaturan privasi.</p>

      <div className="mt-6 grid gap-5">
        <section aria-labelledby="info-akun" className={cardClass}>
          <h2 id="info-akun" className="text-lg font-semibold">
            Info akun
          </h2>
          <dl className="mt-4 grid gap-3 sm:grid-cols-[8rem_1fr]">
            <dt className="text-text-secondary">Nama</dt>
            <dd className="break-words">{profile.name}</dd>
            <dt className="text-text-secondary">Email</dt>
            <dd className="break-all">{profile.email}</dd>
            <dt className="text-text-secondary">Jurusan</dt>
            <dd className="break-words">{profile.major ?? 'Belum diisi'}</dd>
            <dt className="text-text-secondary">Kampus</dt>
            <dd className="break-words">{profile.institution_name ?? 'Belum diisi'}</dd>
          </dl>
        </section>

        <section aria-labelledby="persetujuan-pelatihan" className={cardClass}>
          <h2 id="persetujuan-pelatihan" className="text-lg font-semibold">
            Persetujuan pelatihan
          </h2>
          <p className="mt-2">{CONSENT_TRAINING_QUESTION}</p>
          <p className="mt-2 text-text-secondary">{CONSENT_TRAINING_TEXT}</p>
          <p className="mt-4 font-medium" role="status">
            {statusText(choice, answered)}
          </p>

          {saved ? (
            <Alert className="mt-3">
              <AlertDescription>Pilihanmu tersimpan.</AlertDescription>
            </Alert>
          ) : null}
          {failed ? (
            <Alert variant="destructive" className="mt-3">
              <AlertDescription>{CONSENT_ERROR}</AlertDescription>
            </Alert>
          ) : null}

          <div className="mt-4 flex flex-col gap-3 sm:flex-row">
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
                >
                  {selected ? <Check aria-hidden="true" /> : null}
                  {label}
                </Button>
              );
            })}
          </div>
        </section>

        <section aria-labelledby="hapus-akun" className={cardClass}>
          <h2 id="hapus-akun" className="text-lg font-semibold">
            Hapus akun
          </h2>
          <p className="mt-2 text-text-secondary">
            Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Data yang sudah diekspor untuk pelatihan
            model (bila kamu menyetujuinya) tidak ikut terhapus, dan salinan cadangan hilang otomatis sesuai masa
            simpannya.
          </p>

          {is_only_admin ? (
            <Alert className="mt-4">
              <AlertDescription>
                Kamu satu-satunya admin, jadi akun ini belum bisa dihapus. Jadikan orang lain admin lebih dulu.
              </AlertDescription>
            </Alert>
          ) : null}

          <div className="mt-4">
            <Button type="button" variant="destructive" disabled={is_only_admin} onClick={() => setDeleteOpen(true)}>
              Hapus akun
            </Button>
          </div>
        </section>
      </div>

      <DeleteAccountDialog open={deleteOpen} onClose={() => setDeleteOpen(false)} />
    </AppShell>
  );
}
