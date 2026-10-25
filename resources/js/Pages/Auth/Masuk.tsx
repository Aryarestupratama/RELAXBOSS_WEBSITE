import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { CircleAlert, CircleCheck } from 'lucide-react';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthSubmitButton from '@/Components/shared/AuthSubmitButton';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Input } from '@/Components/ui/input';
import { AUTH_INPUT, AUTH_LINK } from '@/lib/authStyles';

type MasukProps = {
  status: string | null;
};

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
    <GuestLayout title="Masuk" illustration="masuk" tab="masuk">
      <div data-m-enter className="text-center">
        <h1 className="text-[1.625rem] leading-tight font-semibold tracking-tight">Masuk</h1>
        <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-text-secondary">Senang kamu kembali. Masuk untuk melanjutkan.</p>
      </div>

      <form data-m-enter onSubmit={submit} noValidate className="mt-5 space-y-3.5">
        {status === 'password-reset' ? (
          <Alert className="px-3 py-2.5 text-[0.8125rem]">
            <CircleCheck className="text-success!" aria-hidden="true" />
            <AlertDescription className="text-[0.8125rem]">
              Kata sandimu sudah diganti. Silakan masuk dengan kata sandi yang baru.
            </AlertDescription>
          </Alert>
        ) : null}

        {errors.login ? (
          <Alert variant="destructive" className="px-3 py-2.5 text-[0.8125rem]">
            <CircleAlert aria-hidden="true" />
            <AlertDescription className="text-[0.8125rem]">{errors.login}</AlertDescription>
          </Alert>
        ) : null}

        <FormField id="email" label="Email" compact error={errors.email}>
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

        <FormField id="password" label="Kata sandi" compact error={errors.password}>
          {(control) => (
            <PasswordInput
              {...control}
              compact
              name="password"
              autoComplete="current-password"
              required
              value={form.data.password}
              onChange={(event) => form.setData('password', event.target.value)}
            />
          )}
        </FormField>

        <div className="-mt-1 text-right text-[0.8125rem]">
          <Link href="/lupa-sandi" className={AUTH_LINK}>
            Lupa kata sandi?
          </Link>
        </div>

        <AuthSubmitButton processing={form.processing}>Masuk</AuthSubmitButton>
      </form>

      <p data-m-enter className="mt-5 text-center text-[0.8125rem] text-text-secondary">
        Belum punya akun?{' '}
        <Link href="/daftar" className={AUTH_LINK}>
          Daftar
        </Link>
      </p>
    </GuestLayout>
  );
}
