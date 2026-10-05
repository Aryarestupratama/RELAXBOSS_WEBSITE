import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';

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
      <h1>Buat kata sandi baru</h1>
      <p className="mt-2 text-text-secondary">
        {email ? (
          <>
            Untuk akun <strong className="text-text">{email}</strong>. Setelah disimpan, kamu masuk
            dengan kata sandi yang baru.
          </>
        ) : (
          'Setelah disimpan, kamu masuk dengan kata sandi yang baru.'
        )}
      </p>

      <form onSubmit={submit} noValidate className="mt-6 space-y-5">
        {linkInvalid ? (
          <Alert variant="destructive">
            <AlertDescription>
              Tautan reset ini tidak valid atau sudah kedaluwarsa.{' '}
              <Link href="/lupa-sandi" className="font-medium underline underline-offset-4">
                Minta tautan baru
              </Link>
            </AlertDescription>
          </Alert>
        ) : null}

        <FormField
          id="password"
          label="Kata sandi baru"
          hint={`Minimal ${minPasswordLength} karakter.`}
          error={errors.password}
        >
          {(control) => (
            <PasswordInput
              {...control}
              name="password"
              autoComplete="new-password"
              required
              value={form.data.password}
              onChange={(event) => form.setData('password', event.target.value)}
            />
          )}
        </FormField>

        <Button type="submit" className="w-full" disabled={form.processing}>
          {form.processing ? 'Memproses...' : 'Simpan kata sandi'}
        </Button>
      </form>
    </GuestLayout>
  );
}
