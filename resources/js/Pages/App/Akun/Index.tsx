import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Check, CircleAlert, CircleCheck, Info, ShieldCheck, Trash2, User } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import DeleteAccountDialog from '@/Components/shared/DeleteAccountDialog';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { useActiveSection } from '@/lib/useActiveSection';
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

const cardClass = 'rounded-xl border border-border bg-card p-5 shadow-card md:p-6';

const SECTIONS = [
  { id: 'info-akun', label: 'Info akun', icon: User },
  { id: 'persetujuan-pelatihan', label: 'Persetujuan pelatihan', icon: ShieldCheck },
  { id: 'hapus-akun', label: 'Hapus akun', icon: Trash2 },
] as const;

const SECTION_IDS: string[] = SECTIONS.map((section) => section.id);

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

  const activeSection = useActiveSection(SECTION_IDS);
  const initial = profile.name.trim().charAt(0).toUpperCase() || '?';

  return (
    <AppShell title="Akun" staggered>
      <div data-load>
        <h1>Akun</h1>
        <p className="mt-2 text-text-secondary">Data akunmu dan pengaturan privasi.</p>
      </div>

      <div className="mt-6 grid items-start gap-5 lg:grid-cols-[13.75rem_minmax(0,1fr)]">
        <nav aria-label="Bagian akun" data-load className="hidden md:block lg:sticky lg:top-8">
          <ul className="flex gap-2 overflow-x-auto lg:flex-col lg:gap-1.5 lg:rounded-xl lg:border lg:border-border lg:bg-card lg:p-3 lg:shadow-card">
            {SECTIONS.map(({ id, label, icon: Icon }) => {
              const active = activeSection === id;
              const danger = id === 'hapus-akun';
              return (
                <li key={id} className="shrink-0">
                  <a
                    href={`#${id}`}
                    aria-current={active ? 'true' : undefined}
                    className={`inline-flex min-h-11 w-full items-center gap-3 rounded-full border border-border bg-card px-4 text-sm transition-colors hover:bg-neutral-soft lg:rounded-lg lg:border-0 lg:bg-transparent lg:px-3.5 lg:text-base ${
                      active ? 'font-semibold lg:bg-brand-soft/50 lg:hover:bg-brand-soft/50' : ''
                    } ${danger ? 'text-crisis-text' : 'text-text'}`}
                  >
                    <Icon className={`size-5 ${danger ? '' : 'text-brand-strong'}`} aria-hidden="true" />
                    {label}
                  </a>
                </li>
              );
            })}
          </ul>
        </nav>

        <div className="min-w-0 space-y-5">
          <section id="info-akun" aria-labelledby="judul-info-akun" data-load className={`${cardClass} scroll-mt-8`}>
            <h2 id="judul-info-akun" className="text-base font-semibold">
              Info akun
            </h2>

            <div className="mt-5 flex items-center gap-4" aria-hidden="true">
              <span className="grid size-14 shrink-0 select-none place-items-center rounded-full bg-brand-soft text-2xl font-semibold text-text">
                {initial}
              </span>
              <div className="min-w-0">
                <p className="truncate text-base font-semibold">{profile.name}</p>
                <p className="truncate text-text-secondary">{profile.email}</p>
              </div>
            </div>

            <div className="my-5 border-t border-dashed border-border" />

            <dl className="grid gap-4 sm:grid-cols-2">
              {[
                { label: 'Nama', value: profile.name, breakAll: false },
                { label: 'Email', value: profile.email, breakAll: true },
                { label: 'Jurusan', value: profile.major, breakAll: false },
                { label: 'Kampus', value: profile.institution_name, breakAll: false },
              ].map(({ label, value, breakAll }) => (
                <div key={label} className="min-w-0">
                  <dt className="mb-1.5 text-sm text-text-secondary">{label}</dt>
                  <dd
                    className={`rounded-lg bg-neutral-soft px-3.5 py-3 ${breakAll ? 'break-all' : 'break-words'} ${
                      value ? '' : 'text-text-secondary italic'
                    }`}
                  >
                    {value ?? 'Belum diisi'}
                  </dd>
                </div>
              ))}
            </dl>
          </section>

          <section
            id="persetujuan-pelatihan"
            aria-labelledby="judul-persetujuan"
            data-load
            className={`${cardClass} scroll-mt-8`}
          >
            <h2 id="judul-persetujuan" className="text-base font-semibold">
              Persetujuan pelatihan
            </h2>
            <p className="mt-3 text-base font-medium">{CONSENT_TRAINING_QUESTION}</p>
            <p className="mt-2 text-text-secondary">{CONSENT_TRAINING_TEXT}</p>

            <p className="mt-5 flex items-center gap-2 font-medium" role="status">
              {answered && choice !== null ? (
                <CircleCheck className="size-5 shrink-0" aria-hidden="true" />
              ) : (
                <Info className="size-5 shrink-0 text-text-secondary" aria-hidden="true" />
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
                    className={`min-h-11 rounded-full px-6 ${
                      selected ? 'border-2 border-primary bg-mood-yellow font-semibold hover:bg-mood-yellow' : ''
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
            id="hapus-akun"
            aria-labelledby="judul-hapus-akun"
            data-load
            className="scroll-mt-8 rounded-xl border border-destructive/40 bg-destructive/5 p-5 md:p-5"
          >
            <h2 id="judul-hapus-akun" className="text-base font-semibold text-crisis-text">
              Hapus akun
            </h2>
            <p className="mt-2 text-text-secondary">
              Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Data yang sudah diekspor untuk pelatihan
              model (bila kamu menyetujuinya) tidak ikut terhapus, dan salinan cadangan hilang otomatis sesuai masa
              simpannya.
            </p>

            {is_only_admin ? (
              <Alert className="mt-4">
                <Info aria-hidden="true" />
                <AlertDescription>
                  Kamu satu-satunya admin, jadi akun ini belum bisa dihapus. Jadikan orang lain admin lebih dulu.
                </AlertDescription>
              </Alert>
            ) : null}

            <div className="mt-5">
              <Button type="button" variant="destructive" disabled={is_only_admin} onClick={() => setDeleteOpen(true)}>
                Hapus akun
              </Button>
            </div>
          </section>
        </div>
      </div>

      <DeleteAccountDialog open={deleteOpen} onClose={() => setDeleteOpen(false)} />
    </AppShell>
  );
}
