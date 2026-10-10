import { useEffect, type FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { CircleAlert } from 'lucide-react';
import GuestLayout from '@/Layouts/GuestLayout';
import CheckboxField from '@/Components/shared/CheckboxField';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import RequiredMark from '@/Components/shared/RequiredMark';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

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
    <GuestLayout title="Daftar" illustration="daftar" wide>
      <h1>Buat akunmu</h1>
      <p className="mt-2 text-text-secondary">
        Mulai dari satu langkah kecil. Setelah mendaftar, kami kirim tautan verifikasi ke emailmu.
      </p>

      <form onSubmit={submit} noValidate className="mt-5 space-y-4 lg:space-y-3">
        {errors.register ? (
          <Alert variant="destructive">
            <CircleAlert aria-hidden="true" />
            <AlertDescription>{errors.register}</AlertDescription>
          </Alert>
        ) : null}

        <div className="space-y-4 lg:grid lg:grid-cols-2 lg:gap-3 lg:space-y-0">
          <FormField id="name" label="Nama" required error={errors.name}>
            {(control) => (
              <Input
                {...control}
                name="name"
                autoComplete="name"
                required
                value={form.data.name}
                onChange={(event) => form.setData('name', event.target.value)}
              />
            )}
          </FormField>

          <FormField id="email" label="Email" required error={errors.email}>
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
        </div>

        <FormField
          id="password"
          label="Kata sandi"
          required
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

        <div className="space-y-4 lg:grid lg:grid-cols-2 lg:gap-3 lg:space-y-0">
          <FormField
            id="major"
            label="Jurusan (opsional)"
            hint="Membantu RelaxMate memahami konteksmu."
            error={errors.major}
          >
            {(control) => (
              <Input
                {...control}
                name="major"
                autoComplete="off"
                value={form.data.major}
                onChange={(event) => form.setData('major', event.target.value)}
              />
            )}
          </FormField>

          <FormField
            id="institution_name"
            label="Kampus (opsional)"
            hint="Hanya untuk profilmu, tidak dikirim ke AI."
            error={errors.institution_name}
          >
            {(control) => (
              <Input
                {...control}
                name="institution_name"
                autoComplete="organization"
                value={form.data.institution_name}
                onChange={(event) => form.setData('institution_name', event.target.value)}
              />
            )}
          </FormField>
        </div>

        <CheckboxField
          id="terms"
          required
          checked={form.data.terms}
          onChange={(checked) => form.setData('terms', checked)}
          error={errors.terms}
          label={
            <span className="text-sm">
              Saya telah membaca{' '}
              <a href="/ketentuan" target="_blank" rel="noopener noreferrer" className="font-medium text-brand-strong underline underline-offset-4">
                Ketentuan penggunaan
              </a>{' '}
              dan{' '}
              <a href="/privasi" target="_blank" rel="noopener noreferrer" className="font-medium text-brand-strong underline underline-offset-4">
                Kebijakan privasi
              </a>
              .<RequiredMark />
            </span>
          }
        />

        <Button type="submit" className="w-full" disabled={form.processing || !form.data.terms}>
          {form.processing ? 'Memproses...' : 'Daftar'}
        </Button>
      </form>

      <p className="mt-5 text-center text-sm text-text-secondary">
        Sudah punya akun?{' '}
        <Link href="/masuk" className="font-medium text-brand-strong underline underline-offset-4">
          Masuk
        </Link>
      </p>
    </GuestLayout>
  );
}
