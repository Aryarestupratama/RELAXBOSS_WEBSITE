import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthSubmitButton from '@/Components/shared/AuthSubmitButton';
import FormField from '@/Components/shared/FormField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Input } from '@/Components/ui/input';
import { AUTH_INPUT, AUTH_LINK } from '@/lib/authStyles';

type LupaSandiProps = {
  status: string | null;
  expireMinutes: number;
};

/** SCR-009 (form email) */
export default function LupaSandi({ status, expireMinutes }: LupaSandiProps) {
  const form = useForm({ email: '' });
  const errors = form.errors as Partial<Record<string, string>>;

  useEffect(() => {
    if (Object.keys(form.errors).length > 0) {
      document.querySelector<HTMLElement>('[aria-invalid="true"]')?.focus();
    }
  }, [form.errors]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/lupa-sandi');
  };

  return (
    <GuestLayout title="Lupa Kata Sandi">
      <div data-m-enter className="text-center">
        <h1 className="text-[1.625rem] leading-tight font-semibold tracking-tight">Lupa kata sandi?</h1>
        <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-text-secondary">Masukkan emailmu. Kalau terdaftar, kami kirim tautan untuk membuat kata sandi baru.</p>
      </div>

      <form data-m-enter onSubmit={submit} noValidate className="mt-5 space-y-3.5">
        {status === 'reset-link-sent' ? (
          <Alert className="px-3 py-2.5 text-[0.8125rem]">
            <AlertDescription className="text-[0.8125rem]">
              Kalau email itu terdaftar, tautan untuk mengatur ulang kata sandi sudah kami kirim. Cek
              kotak masuk dan folder spam.
            </AlertDescription>
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

        <AuthSubmitButton processing={form.processing}>Kirim tautan</AuthSubmitButton>

        <p className="text-[0.8125rem] text-text-secondary">
          Tautannya berlaku {expireMinutes} menit. Belum ada emailnya? Periksa folder spam dulu.
        </p>
      </form>

      <p data-m-enter className="mt-5 text-center text-[0.8125rem] text-text-secondary">
        Ingat kata sandimu?{' '}
        <Link href="/masuk" className={AUTH_LINK}>
          Kembali masuk
        </Link>
      </p>
    </GuestLayout>
  );
}
