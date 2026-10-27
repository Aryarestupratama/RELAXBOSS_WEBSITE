import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { CircleAlert } from 'lucide-react';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthSubmitButton from '@/Components/shared/AuthSubmitButton';
import AuthCheckbox from '@/Components/shared/AuthCheckbox';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import RequiredMark from '@/Components/shared/RequiredMark';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Input } from '@/Components/ui/input';
import { AUTH_INPUT, AUTH_LINK } from '@/lib/authStyles';

type DaftarProps = {
  minPasswordLength: number;
};

/** SCR-007 */
export default function Daftar({ minPasswordLength }: DaftarProps) {
  const form = useForm({
    name: '',
    email: '',
    password: '',
    major: '',
    institution_name: '',
    terms: false,
  });
  const errors = form.errors as Partial<Record<string, string>>;

  // Galat validasi: fokus pindah ke kolom pertama yang salah (Design bagian 5).
  useEffect(() => {
    if (Object.keys(form.errors).length > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [form.errors]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/daftar', { onFinish: () => form.reset('password') });
  };

  return (
    <GuestLayout title="Daftar" illustration="daftar" tab="daftar" wide>
      <div data-m-enter className="text-center">
        <h1 className="text-[1.625rem] leading-tight font-semibold tracking-tight">Buat akunmu</h1>
        <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-text-secondary">Mulai dari satu langkah kecil. Setelah mendaftar, kami kirim tautan verifikasi ke emailmu.</p>
      </div>

      <form data-m-enter onSubmit={submit} noValidate className="mt-5 space-y-3">
        {errors.register ? (
          <Alert variant="destructive" className="px-3 py-2.5 text-[0.8125rem]">
            <CircleAlert aria-hidden="true" />
            <AlertDescription className="text-[0.8125rem]">{errors.register}</AlertDescription>
          </Alert>
        ) : null}

        <div className="space-y-3 sm:grid sm:grid-cols-2 sm:items-start sm:gap-3 sm:space-y-0">
          <FormField id="name" label="Nama" required compact error={errors.name}>
            {(control) => (
              <Input
                {...control}
                name="name"
                autoComplete="name"
                required
                className={AUTH_INPUT}
                value={form.data.name}
                onChange={(event) => form.setData('name', event.target.value)}
              />
            )}
          </FormField>

          <FormField id="email" label="Email" required compact error={errors.email}>
            {(control) => (
              <Input
                {...control}
                name="email"
                type="email"
                autoComplete="email"
                required
                className={AUTH_INPUT}
                value={form.data.email}
                onChange={(event) => form.setData('email', event.target.value)}
              />
            )}
          </FormField>
        </div>

        <FormField
          id="password"
          label="Kata sandi"
          required
          compact
          hint={`Minimal ${minPasswordLength} karakter.`}
          error={errors.password}
        >
          {(control) => (
            <PasswordInput
              {...control}
              compact
              name="password"
              autoComplete="new-password"
              required
              value={form.data.password}
              onChange={(event) => form.setData('password', event.target.value)}
            />
          )}
        </FormField>

        <div className="space-y-3 sm:grid sm:grid-cols-2 sm:items-start sm:gap-3 sm:space-y-0">
          <FormField
            id="major"
            label="Jurusan (opsional)"
            compact
            hint="Untuk konteks RelaxMate."
            error={errors.major}
          >
            {(control) => (
              <Input
                {...control}
                name="major"
                autoComplete="off"
                className={AUTH_INPUT}
                value={form.data.major}
                onChange={(event) => form.setData('major', event.target.value)}
              />
            )}
          </FormField>

          <FormField
            id="institution_name"
            label="Kampus (opsional)"
            compact
            hint="Tidak dikirim ke AI."
            error={errors.institution_name}
          >
            {(control) => (
              <Input
                {...control}
                name="institution_name"
                autoComplete="organization"
                className={AUTH_INPUT}
                value={form.data.institution_name}
                onChange={(event) => form.setData('institution_name', event.target.value)}
              />
            )}
          </FormField>
        </div>

        <AuthCheckbox
          id="terms"
          required
          checked={form.data.terms}
          onChange={(checked) => form.setData('terms', checked)}
          error={errors.terms}
          label={
            <span className="text-[0.8125rem] leading-snug text-text-secondary lg:whitespace-nowrap">
              Saya telah membaca{' '}
              <a href="/ketentuan" target="_blank" rel="noopener noreferrer" className={AUTH_LINK}>
                Ketentuan
              </a>{' '}
              dan{' '}
              <span className="whitespace-nowrap">
                <a href="/privasi" target="_blank" rel="noopener noreferrer" className={AUTH_LINK}>
                  Kebijakan privasi
                </a>
                .<RequiredMark />
              </span>
            </span>
          }
        />

        <AuthSubmitButton processing={form.processing} disabled={!form.data.terms}>
          Daftar
        </AuthSubmitButton>
      </form>

      <p data-m-enter className="mt-4 text-center text-[0.8125rem] text-text-secondary">
        Sudah punya akun?{' '}
        <Link href="/masuk" className={AUTH_LINK}>
          Masuk
        </Link>
      </p>
    </GuestLayout>
  );
}
