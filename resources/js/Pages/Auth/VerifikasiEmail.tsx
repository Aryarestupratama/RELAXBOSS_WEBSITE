import type { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import AuthSubmitButton from '@/Components/shared/AuthSubmitButton';
import LogoutButton from '@/Components/shared/LogoutButton';
import { Alert, AlertDescription } from '@/Components/ui/alert';

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
      <div data-m-enter className="text-center">
        <h1 className="text-[1.625rem] leading-tight font-semibold tracking-tight">Cek emailmu</h1>
        {sendFailed ? null : (
          <p className="mt-1.5 text-[0.8125rem] leading-relaxed text-text-secondary">
            Kami mengirim tautan verifikasi ke <strong className="text-text">{email}</strong>. Klik
            tautannya dalam {expireMinutes} menit untuk mengaktifkan akunmu.
          </p>
        )}
      </div>

      <div data-m-enter className="mt-5 space-y-3.5">
        {status === 'verification-link-sent' ? (
          <Alert className="px-3 py-2.5 text-[0.8125rem]">
            <AlertDescription className="text-[0.8125rem]">
              Email verifikasi sudah dikirim. Cek kotak masuk dan folder spam.
            </AlertDescription>
          </Alert>
        ) : null}

        {sendFailed ? (
          <Alert variant="destructive" className="px-3 py-2.5 text-[0.8125rem]">
            <AlertDescription className="text-[0.8125rem]">
              Akunmu sudah dibuat, tetapi email belum berhasil terkirim. Tekan &quot;Kirim ulang
              email&quot; untuk mencoba lagi.
            </AlertDescription>
          </Alert>
        ) : null}

        {errors.resend ? (
          <Alert variant="destructive" className="px-3 py-2.5 text-[0.8125rem]">
            <AlertDescription className="text-[0.8125rem]">{errors.resend}</AlertDescription>
          </Alert>
        ) : null}

        <form onSubmit={resend}>
          <AuthSubmitButton processing={form.processing}>Kirim ulang email</AuthSubmitButton>
        </form>

        <p className="text-center text-[0.8125rem] text-text-secondary">
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
