import type { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import LogoutButton from '@/Components/shared/LogoutButton';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';

type VerifikasiEmailProps = {
  email: string;
  expireMinutes: number;
  resendPerHour: number;
  status: string | null;
};

/** SCR-010 */
export default function VerifikasiEmail({
  email,
  expireMinutes,
  resendPerHour,
  status,
}: VerifikasiEmailProps) {
  const form = useForm({});
  const errors = form.errors as Partial<Record<string, string>>;
  const sendFailed = status === 'verification-send-failed';

  const resend = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/verifikasi-email/kirim-ulang');
  };

  return (
    <GuestLayout title="Verifikasi Email">
      <h1>Cek emailmu</h1>
      {sendFailed ? null : (
        <p className="mt-2 text-text-secondary">
          Kami mengirim tautan verifikasi ke <strong className="text-text">{email}</strong>. Klik
          tautannya dalam {expireMinutes} menit untuk mengaktifkan akunmu.
        </p>
      )}

      <div className="mt-6 space-y-4">
        {status === 'verification-link-sent' ? (
          <Alert>
            <AlertDescription>
              Email verifikasi sudah dikirim. Cek kotak masuk dan folder spam.
            </AlertDescription>
          </Alert>
        ) : null}

        {sendFailed ? (
          <Alert variant="destructive">
            <AlertDescription>
              Akunmu sudah dibuat, tetapi email belum berhasil terkirim. Tekan &quot;Kirim ulang
              email&quot; untuk mencoba lagi.
            </AlertDescription>
          </Alert>
        ) : null}

        {errors.resend ? (
          <Alert variant="destructive">
            <AlertDescription>{errors.resend}</AlertDescription>
          </Alert>
        ) : null}

        <form onSubmit={resend}>
          <Button type="submit" className="w-full" disabled={form.processing}>
            {form.processing ? 'Memproses...' : 'Kirim ulang email'}
          </Button>
        </form>

        <p className="text-sm text-text-secondary">
          Belum ada emailnya? Periksa folder spam dulu. Kirim ulang dibatasi {resendPerHour} kali
          per jam.
        </p>

        <div className="flex justify-center">
          <LogoutButton />
        </div>
      </div>
    </GuestLayout>
  );
}
