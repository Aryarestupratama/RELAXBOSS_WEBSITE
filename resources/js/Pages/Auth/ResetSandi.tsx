import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthSubmitButton from '@/Components/shared/AuthSubmitButton';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { AUTH_LINK } from '@/lib/authStyles';

type ResetSandiProps = {
  token: string;
  email: string;
  minPasswordLength: number;
};

/** SCR-009 (kolom sandi baru) */
export default function ResetSandi({ token, email, minPasswordLength }: ResetSandiProps) {
  const form = useForm({ token, email, password: '' });
  const errors = form.errors as Partial<Record<string, string>>;
  const linkInvalid = Boolean(errors.reset || errors.token || errors.email);

  useEffect(() => {
    if (Object.keys(form.errors).length > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [form.errors]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/reset-sandi', { onFinish: () => form.reset('password') });
  };

  return (
    <GuestLayout title="Kata Sandi Baru">
      <div data-m-enter className="text-center">
        <h1 className="text-[1.625rem] leading-tight font-semibold tracking-tight">Buat kata sandi baru</h1>
        <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-text-secondary">
          {email ? (
            <>
              Untuk akun <strong className="text-text">{email}</strong>. Setelah disimpan, kamu masuk
              dengan kata sandi yang baru.
            </>
          ) : (
            'Setelah disimpan, kamu masuk dengan kata sandi yang baru.'
          )}
        </p>
      </div>

      <form data-m-enter onSubmit={submit} noValidate className="mt-5 space-y-3.5">
        {linkInvalid ? (
          <Alert variant="destructive" className="px-3 py-2.5 text-[0.8125rem]">
            <AlertDescription className="text-[0.8125rem]">
              Tautan reset ini tidak valid atau sudah kedaluwarsa.{' '}
              <Link href="/lupa-sandi" className={AUTH_LINK}>
                Minta tautan baru
              </Link>
            </AlertDescription>
          </Alert>
        ) : null}

        <FormField
          id="password"
          label="Kata sandi baru"
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

        <AuthSubmitButton processing={form.processing}>Simpan kata sandi</AuthSubmitButton>
      </form>
    </GuestLayout>
  );
}
