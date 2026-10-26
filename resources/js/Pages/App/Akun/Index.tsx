import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { Check, CircleAlert, CircleCheck, Info, Trash2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import DeleteAccountDialog from '@/Components/shared/DeleteAccountDialog';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
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

  const infoRows = [
    { label: 'Nama', value: profile.name, breakAll: false },
    { label: 'Email', value: profile.email, breakAll: true },
    { label: 'Jurusan', value: profile.major, breakAll: false },
    { label: 'Kampus', value: profile.institution_name, breakAll: false },
  ];

  return (
    <AppShell title="Akun" wide staggered>
      <PageHeader title="Akun" description="Data akunmu dan pengaturan privasi." />

      <div className="min-w-0 space-y-4">
        <Card data-load role="region" aria-labelledby="judul-info-akun" className="gap-0 overflow-hidden">
          <div className="flex items-center gap-4 bg-soft-lavender px-5 py-4" aria-hidden="true">
            <Avatar>
              <AvatarFallback>{initial}</AvatarFallback>
            </Avatar>
            <div className="min-w-0">
              <p className="truncate text-base font-bold text-text">{profile.name}</p>
              <p className="truncate text-[0.8125rem] text-text-secondary">{profile.email}</p>
            </div>
          </div>

          <div className="flex flex-col gap-4 p-5">
            <CardHeader>
              <CardTitle id="judul-info-akun" role="heading" aria-level={2} className="text-base">
                Info akun
              </CardTitle>
            </CardHeader>
            <CardContent>
              <dl className="grid gap-3 sm:grid-cols-2">
                {infoRows.map(({ label, value, breakAll }) => (
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
            </CardContent>
          </div>
        </Card>

        <div className="grid items-start gap-4 xl:grid-cols-5">
          <Card data-load role="region" aria-labelledby="judul-persetujuan" className="gap-4 p-5 xl:col-span-3">
            <CardHeader className="flex-row flex-wrap items-center justify-between gap-2">
              <CardTitle id="judul-persetujuan" role="heading" aria-level={2} className="text-base">
                Persetujuan pelatihan
              </CardTitle>
              <Badge variant={answered && choice !== null ? 'mint' : 'secondary'} role="status" className="py-1 whitespace-normal">
                {answered && choice !== null ? (
                  <CircleCheck aria-hidden="true" />
                ) : (
                  <Info aria-hidden="true" />
                )}
                {statusText(choice, answered)}
              </Badge>
            </CardHeader>
            <Separator />

            <CardContent className="space-y-1">
              <p className="text-sm font-semibold">{CONSENT_TRAINING_QUESTION}</p>
              <p className="text-[0.8125rem] leading-relaxed text-text-secondary">{CONSENT_TRAINING_TEXT}</p>
            </CardContent>

            {saved ? (
              <Alert>
                <CircleCheck className="text-success!" aria-hidden="true" />
                <AlertDescription>Pilihanmu tersimpan.</AlertDescription>
              </Alert>
            ) : null}
            {failed ? (
              <Alert variant="destructive">
                <CircleAlert aria-hidden="true" />
                <AlertDescription>{CONSENT_ERROR}</AlertDescription>
              </Alert>
            ) : null}

            <CardFooter className="flex-col items-stretch gap-2 sm:flex-row">
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
            </CardFooter>
          </Card>

          <Card
            data-load
            role="region"
            aria-labelledby="judul-hapus-akun"
            className="gap-4 border-destructive/40 bg-crisis-bg p-5 xl:col-span-2"
          >
            <CardHeader>
              <CardTitle
                id="judul-hapus-akun"
                role="heading"
                aria-level={2}
                className="flex items-center gap-2 text-base text-crisis-text"
              >
                <Trash2 className="size-4.5" aria-hidden="true" />
                Hapus akun
              </CardTitle>
              <CardDescription className="text-[0.8125rem] leading-relaxed">
                Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Data yang sudah diekspor untuk
                pelatihan model (bila kamu menyetujuinya) tidak ikut terhapus, dan salinan cadangan hilang otomatis
                sesuai masa simpannya.
              </CardDescription>
            </CardHeader>

            {is_only_admin ? (
              <Alert>
                <Info aria-hidden="true" />
                <AlertDescription>
                  Kamu satu-satunya admin, jadi akun ini belum bisa dihapus. Jadikan orang lain admin lebih dulu.
                </AlertDescription>
              </Alert>
            ) : null}

            <CardFooter>
              <Button
                type="button"
                variant="destructive"
                className="rounded-full! px-5 text-sm"
                disabled={is_only_admin}
                onClick={() => setDeleteOpen(true)}
              >
                Hapus akun
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>

      <DeleteAccountDialog open={deleteOpen} onClose={() => setDeleteOpen(false)} />
    </AppShell>
  );
}
