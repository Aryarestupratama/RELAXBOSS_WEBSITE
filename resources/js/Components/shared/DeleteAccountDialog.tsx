import { useEffect, useRef, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';

type DeleteAccountDialogProps = {
  open: boolean;
  onClose: () => void;
};

/**
 * Konfirmasi hapus akun (API-016). Memakai elemen <dialog> bawaan (bukan Radix) agar aman untuk CSP bernonce,
 * sama seperti ConfirmLeaveDialog dan ConsentDialog. Setelah berhasil, server mengarahkan ke Beranda (kunjungan penuh).
 */
export default function DeleteAccountDialog({ open, onClose }: DeleteAccountDialogProps) {
  const ref = useRef<HTMLDialogElement>(null);
  const form = useForm({ password: '' });
  const errors = form.errors as Partial<Record<string, string>>;

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) {
      return;
    }
    if (open && !dialog.open) {
      dialog.showModal();
      dialog.querySelector<HTMLInputElement>('input')?.focus();
    }
    if (!open && dialog.open) {
      dialog.close();
      form.reset();
      form.clearErrors();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  useEffect(() => {
    if (errors.password) {
      ref.current?.querySelector<HTMLInputElement>('input')?.focus();
    }
  }, [errors.password]);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.delete('/app/akun', {
      preserveScroll: true,
      onFinish: () => form.reset('password'),
    });
  };

  return (
    <dialog
      ref={ref}
      aria-labelledby="delete-account-title"
      aria-describedby="delete-account-desc"
      onCancel={(event) => {
        event.preventDefault();
        if (!form.processing) {
          onClose();
        }
      }}
      className="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground shadow-popover backdrop:bg-primary/40"
    >
      <h2 id="delete-account-title" className="text-lg font-semibold">
        Hapus akun?
      </h2>
      <p id="delete-account-desc" className="mt-2 text-text-secondary">
        Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Masukkan kata sandi untuk melanjutkan.
      </p>

      <form onSubmit={submit} noValidate className="mt-5 space-y-5">
        {errors.account ? (
          <Alert variant="destructive">
            <AlertDescription>{errors.account}</AlertDescription>
          </Alert>
        ) : null}

        <FormField id="delete-account-password" label="Kata sandi" error={errors.password}>
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

        <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
          <Button type="button" variant="ghost" onClick={onClose} disabled={form.processing}>
            Batal
          </Button>
          <Button type="submit" variant="destructive" disabled={form.processing}>
            {form.processing ? 'Memproses...' : 'Hapus akun saya'}
          </Button>
        </div>
      </form>
    </dialog>
  );
}
