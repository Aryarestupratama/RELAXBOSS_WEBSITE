import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

type MasukProps = {
  status: string | null;
};

const linkClass = 'font-medium text-brand-strong underline underline-offset-4';

/** SCR-008 */
export default function Masuk({ status }: MasukProps) {
  const form = useForm({ email: '', password: '' });
  const errors = form.errors as Partial<Record<string, string>>;

  useEffect(() => {
    if (Object.keys(form.errors).length > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [form.errors]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/masuk', { onFinish: () => form.reset('password') });
  };

  return (
    <GuestLayout title="Masuk">
      <h1>Masuk</h1>
      <p className="mt-2 text-text-secondary">Senang kamu kembali. Masuk untuk melanjutkan.</p>

      <form onSubmit={submit} noValidate className="mt-6 space-y-5">
        {status === 'password-reset' ? (
          <Alert>
            <AlertDescription>
              Kata sandimu sudah diganti. Silakan masuk dengan kata sandi yang baru.
            </AlertDescription>
          </Alert>
        ) : null}

        {errors.login ? (
          <Alert variant="destructive">
            <AlertDescription>{errors.login}</AlertDescription>
          </Alert>
        ) : null}

        <FormField id="email" label="Email" error={errors.email}>
          {(control) => (
            <Input
              {...control}
              name="email"
              type="email"
              autoComplete="email"
              required
              value={form.data.email}
              onChange={(event) => form.setData('email', event.target.value)}
            />
          )}
        </FormField>

        <FormField id="password" label="Kata sandi" error={errors.password}>
          {(control) => (
            <PasswordInput
              {...control}
              name="password"
              autoComplete="current-password"
              required
              value={form.data.password}
              onChange={(event) => form.setData('password', event.target.value)}
            />
          )}
        </FormField>

        <div className="text-right text-sm">
          <Link href="/lupa-sandi" className={linkClass}>
            Lupa kata sandi?
          </Link>
        </div>

        <Button type="submit" className="w-full" disabled={form.processing}>
          {form.processing ? 'Memproses...' : 'Masuk'}
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-text-secondary">
        Belum punya akun?{' '}
        <Link href="/daftar" className={linkClass}>
          Daftar
        </Link>
      </p>
    </GuestLayout>
  );
}
