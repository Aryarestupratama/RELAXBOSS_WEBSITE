import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import FormField from '@/Components/shared/FormField';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

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
      <h1>Lupa kata sandi?</h1>
      <p className="mt-2 text-text-secondary">
        Masukkan emailmu. Kalau terdaftar, kami kirim tautan untuk membuat kata sandi baru.
      </p>

      <form onSubmit={submit} noValidate className="mt-6 space-y-5">
        {status === 'reset-link-sent' ? (
          <Alert>
            <AlertDescription>
              Kalau email itu terdaftar, tautan untuk mengatur ulang kata sandi sudah kami kirim. Cek
              kotak masuk dan folder spam.
            </AlertDescription>
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

        <Button type="submit" className="w-full" disabled={form.processing}>
          {form.processing ? 'Memproses...' : 'Kirim tautan'}
        </Button>

        <p className="text-sm text-text-secondary">
          Tautannya berlaku {expireMinutes} menit. Belum ada emailnya? Periksa folder spam dulu.
        </p>
      </form>

      <p className="mt-6 text-center text-sm text-text-secondary">
        Ingat kata sandimu?{' '}
        <Link href="/masuk" className="font-medium text-brand-strong underline underline-offset-4">
          Kembali masuk
        </Link>
      </p>
    </GuestLayout>
  );
}
