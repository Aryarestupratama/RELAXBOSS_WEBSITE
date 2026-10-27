import { useEffect, useRef, useState, type FormEvent } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import { Camera, Check, CircleAlert, CircleCheck, Info, KeyRound, Pencil, Trash2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import DeleteAccountDialog from '@/Components/shared/DeleteAccountDialog';
import FormField from '@/Components/shared/FormField';
import InfoItem from '@/Components/shared/InfoItem';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
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
  avatar_url: string | null;
  is_only_admin: boolean;
  status: string | null;
};

const STATUS_TEXT: Record<string, string> = {
  'profile-updated': 'Profilmu tersimpan.',
  'password-updated': 'Kata sandi diganti. Perangkat lain otomatis keluar.',
  'avatar-updated': 'Foto profil diganti.',
  'avatar-removed': 'Foto profil dihapus.',
};

const AVATAR_MAX_BYTES = 2 * 1024 * 1024;
const PILL = 'rounded-full! px-5 text-sm';

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
export default function Index({ profile, avatar_url, is_only_admin, status }: IndexProps) {
  const { consent } = usePage<SharedConsentProps>().props;
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const [failed, setFailed] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [avatarBusy, setAvatarBusy] = useState(false);
  const fileRef = useRef<HTMLInputElement>(null);
  const { errors: pageErrors } = usePage().props as { errors: Partial<Record<string, string>> };
  const [avatarError, setAvatarError] = useState<string | null>(null);
  const [editingProfile, setEditingProfile] = useState(false);
  const [editingPassword, setEditingPassword] = useState(false);
  const prevEditingProfile = useRef(false);
  const prevEditingPassword = useRef(false);

  const profileForm = useForm({
    name: profile.name,
    major: profile.major ?? '',
    institution_name: profile.institution_name ?? '',
  });
  const profileErrors = profileForm.errors as Partial<Record<string, string>>;

  const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
  const passwordErrors = passwordForm.errors as Partial<Record<string, string>>;

  const flashText = status ? STATUS_TEXT[status] : undefined;

  const submitProfile = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    profileForm.patch('/app/akun/profil', {
      preserveScroll: true,
      onSuccess: () => setEditingProfile(false),
    });
  };

  const cancelProfile = () => {
    profileForm.reset();
    profileForm.clearErrors();
    setEditingProfile(false);
  };

  const cancelPassword = () => {
    passwordForm.reset();
    passwordForm.clearErrors();
    setEditingPassword(false);
  };

  const submitPassword = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    passwordForm.put('/app/akun/sandi', {
      preserveScroll: true,
      onSuccess: () => {
        passwordForm.reset();
        setEditingPassword(false);
      },
      onError: () => passwordForm.reset('password', 'password_confirmation'),
    });
  };

  const pickAvatar = (file: File | undefined) => {
    setAvatarError(null);
    if (!file) {
      return;
    }
    if (file.size > AVATAR_MAX_BYTES) {
      setAvatarError('Ukuran foto maksimal 2 MB.');
      return;
    }
    router.post(
      '/app/akun/foto',
      { avatar: file },
      {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => setAvatarBusy(true),
        onFinish: () => {
          setAvatarBusy(false);
          if (fileRef.current) {
            fileRef.current.value = '';
          }
        },
      },
    );
  };

  const removeAvatar = () => {
    setAvatarError(null);
    router.delete('/app/akun/foto', {
      preserveScroll: true,
      onStart: () => setAvatarBusy(true),
      onFinish: () => setAvatarBusy(false),
    });
  };

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

  // Fokus mengikuti mode: ke kolom pertama saat mulai mengubah, kembali ke tombol saat selesai.
  // Membandingkan dengan nilai sebelumnya agar tidak memindahkan fokus saat halaman pertama dimuat.
  useEffect(() => {
    if (prevEditingProfile.current !== editingProfile) {
      prevEditingProfile.current = editingProfile;
      document.getElementById(editingProfile ? 'akun-nama' : 'tombol-ubah-profil')?.focus();
    }
  }, [editingProfile]);

  useEffect(() => {
    if (prevEditingPassword.current !== editingPassword) {
      prevEditingPassword.current = editingPassword;
      document.getElementById(editingPassword ? 'sandi-sekarang' : 'tombol-ubah-sandi')?.focus();
    }
  }, [editingPassword]);

  return (
    <AppShell title="Akun" staggered>
      <div className="mx-auto w-full max-w-2xl min-w-0">
        <PageHeader title="Akun" description="Data akunmu dan pengaturan privasi." />

        <div className="min-w-0 space-y-4">
          {flashText ? (
            <Alert role="status" data-load>
              <CircleCheck className="text-success!" aria-hidden="true" />
              <AlertDescription>{flashText}</AlertDescription>
            </Alert>
          ) : null}

          <Card data-load role="region" aria-labelledby="judul-profil" className="gap-5 p-5">
            <CardHeader className="flex-row items-center justify-between gap-3">
              <CardTitle id="judul-profil" role="heading" aria-level={2} className="text-base">
                Profil
              </CardTitle>
              {editingProfile ? null : (
                <Button
                  id="tombol-ubah-profil"
                  type="button"
                  variant="outline"
                  className={PILL}
                  onClick={() => setEditingProfile(true)}
                >
                  <Pencil aria-hidden="true" />
                  Ubah profil
                </Button>
              )}
            </CardHeader>
            <Separator />

            <div className="flex flex-wrap items-center gap-4">
              <Avatar aria-hidden="true" className="size-16">
                {avatar_url ? <AvatarImage src={avatar_url} /> : <AvatarFallback className="bg-soft-lavender">{initial}</AvatarFallback>}
              </Avatar>
              <div className="min-w-0 flex-1 basis-48">
                <input
                  ref={fileRef}
                  id="foto-profil"
                  type="file"
                  accept="image/jpeg,image/png,image/webp"
                  className="sr-only"
                  tabIndex={-1}
                  aria-label="Pilih foto profil"
                  onChange={(event) => pickAvatar(event.target.files?.[0])}
                />
                <div className="flex flex-wrap gap-2">
                  <Button type="button" variant="outline" className={PILL} disabled={avatarBusy} onClick={() => fileRef.current?.click()}>
                    <Camera aria-hidden="true" />
                    {avatarBusy ? 'Memproses...' : avatar_url ? 'Ganti foto' : 'Unggah foto'}
                  </Button>
                  {avatar_url ? (
                    <Button type="button" variant="ghost" className={PILL} disabled={avatarBusy} onClick={removeAvatar}>
                      Hapus foto
                    </Button>
                  ) : null}
                </div>
                <p className="mt-2 text-xs text-text-secondary">
                  Fotomu hanya terlihat olehmu. JPG, PNG, atau WebP, maksimal 2 MB.
                </p>
              </div>
            </div>
            {avatarError || pageErrors.avatar ? (
              <Alert variant="destructive">
                <CircleAlert aria-hidden="true" />
                <AlertDescription>{avatarError ?? pageErrors.avatar}</AlertDescription>
              </Alert>
            ) : null}

            {editingProfile ? (
              <form onSubmit={submitProfile} noValidate className="flex flex-col gap-5">
                <CardContent className="grid gap-4 sm:grid-cols-2">
                  <FormField id="akun-nama" label="Nama" required error={profileErrors.name}>
                    {(control) => (
                      <Input
                        {...control}
                        name="name"
                        autoComplete="name"
                        maxLength={100}
                        value={profileForm.data.name}
                        onChange={(event) => profileForm.setData('name', event.target.value)}
                      />
                    )}
                  </FormField>
                  <div className="min-w-0">
                    <p className="mb-1.5 text-sm font-medium">Email</p>
                    <p className="min-h-11 rounded-lg border border-border/60 bg-card px-3.5 py-2.5 text-sm break-all text-text-secondary">
                      {profile.email}
                    </p>
                    <p className="mt-1.5 text-xs text-text-secondary">Email belum bisa diubah.</p>
                  </div>
                  <FormField id="akun-jurusan" label="Jurusan" error={profileErrors.major}>
                    {(control) => (
                      <Input
                        {...control}
                        name="major"
                        maxLength={100}
                        value={profileForm.data.major}
                        onChange={(event) => profileForm.setData('major', event.target.value)}
                      />
                    )}
                  </FormField>
                  <FormField id="akun-kampus" label="Kampus" error={profileErrors.institution_name}>
                    {(control) => (
                      <Input
                        {...control}
                        name="institution_name"
                        maxLength={150}
                        value={profileForm.data.institution_name}
                        onChange={(event) => profileForm.setData('institution_name', event.target.value)}
                      />
                    )}
                  </FormField>
                </CardContent>
                <CardFooter className="justify-end gap-2">
                  <Button type="button" variant="outline" className={PILL} disabled={profileForm.processing} onClick={cancelProfile}>
                    Batal
                  </Button>
                  <Button type="submit" className={PILL} disabled={profileForm.processing || !profileForm.isDirty}>
                    {profileForm.processing ? 'Menyimpan...' : 'Simpan'}
                  </Button>
                </CardFooter>
              </form>
            ) : (
              <dl className="grid gap-4 sm:grid-cols-2">
                <InfoItem label="Nama" value={profile.name} />
                <InfoItem label="Email" value={profile.email} className="break-all" />
                <InfoItem label="Jurusan" value={profile.major} />
                <InfoItem label="Kampus" value={profile.institution_name} />
              </dl>
            )}
          </Card>

          <Card data-load role="region" aria-labelledby="judul-sandi" className="gap-5 p-5">
            <CardHeader className="flex-row items-center justify-between gap-3">
              <div className="min-w-0">
                <CardTitle id="judul-sandi" role="heading" aria-level={2} className="flex items-center gap-2 text-base">
                  <KeyRound className="size-4.5" aria-hidden="true" />
                  Keamanan
                </CardTitle>
                <CardDescription className="mt-1 text-[0.8125rem]">
                  Setelah kata sandi diganti, perangkat lain yang sedang masuk akan keluar otomatis.
                </CardDescription>
              </div>
              {editingPassword ? null : (
                <Button
                  id="tombol-ubah-sandi"
                  type="button"
                  variant="outline"
                  className={`${PILL} shrink-0`}
                  onClick={() => setEditingPassword(true)}
                >
                  Ganti kata sandi
                </Button>
              )}
            </CardHeader>

            {editingPassword ? (
              <form onSubmit={submitPassword} noValidate className="flex flex-col gap-5">
                <Separator />
                <CardContent className="grid gap-4">
                  <FormField id="sandi-sekarang" label="Kata sandi saat ini" required error={passwordErrors.current_password}>
                    {(control) => (
                      <PasswordInput
                        {...control}
                        name="current_password"
                        autoComplete="current-password"
                        value={passwordForm.data.current_password}
                        onChange={(event) => passwordForm.setData('current_password', event.target.value)}
                      />
                    )}
                  </FormField>
                  <FormField id="sandi-baru" label="Kata sandi baru" required error={passwordErrors.password}>
                    {(control) => (
                      <PasswordInput
                        {...control}
                        name="password"
                        autoComplete="new-password"
                        value={passwordForm.data.password}
                        onChange={(event) => passwordForm.setData('password', event.target.value)}
                      />
                    )}
                  </FormField>
                  <FormField id="sandi-konfirmasi" label="Ulangi kata sandi baru" required>
                    {(control) => (
                      <PasswordInput
                        {...control}
                        name="password_confirmation"
                        autoComplete="new-password"
                        value={passwordForm.data.password_confirmation}
                        onChange={(event) => passwordForm.setData('password_confirmation', event.target.value)}
                      />
                    )}
                  </FormField>
                </CardContent>
                <CardFooter className="justify-end gap-2">
                  <Button type="button" variant="outline" className={PILL} disabled={passwordForm.processing} onClick={cancelPassword}>
                    Batal
                  </Button>
                  <Button type="submit" className={PILL} disabled={passwordForm.processing}>
                    {passwordForm.processing ? 'Menyimpan...' : 'Simpan'}
                  </Button>
                </CardFooter>
              </form>
            ) : null}
          </Card>

          <Card data-load role="region" aria-labelledby="judul-persetujuan" className="gap-4 p-5">
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
            className="gap-4 border-destructive/40 bg-crisis-bg p-5"
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
