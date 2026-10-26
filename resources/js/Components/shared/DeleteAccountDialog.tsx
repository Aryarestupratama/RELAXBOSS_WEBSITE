import { useEffect, useRef, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import FormField from '@/Components/shared/FormField';
import PasswordInput from '@/Components/shared/PasswordInput';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogClose, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';

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
    <Dialog
      ref={ref}
      aria-labelledby="delete-account-title"
      aria-describedby="delete-account-desc"
      onCancel={(event) => {
        event.preventDefault();
        if (!form.processing) {
          onClose();
        }
      }}
    >
      <DialogHeader className="relative pr-10">
        <DialogTitle id="delete-account-title">Hapus akun?</DialogTitle>
        <DialogDescription id="delete-account-desc" className="text-[0.8125rem]">
          Semua datamu akan dihapus permanen dan tidak bisa dikembalikan. Masukkan kata sandi untuk melanjutkan.
        </DialogDescription>
        <DialogClose onClick={onClose} disabled={form.processing} className="absolute top-0 right-0 mt-0 -mr-2" />
      </DialogHeader>

      <form onSubmit={submit} noValidate className="mt-4 space-y-4">
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

        <DialogFooter className="mt-0">
          <Button type="button" variant="ghost" className="rounded-full! px-5 text-sm" onClick={onClose} disabled={form.processing}>
            Batal
          </Button>
          <Button type="submit" variant="destructive" className="rounded-full! px-5 text-sm" disabled={form.processing}>
            {form.processing ? 'Memproses...' : 'Hapus akun saya'}
          </Button>
        </DialogFooter>
      </form>
    </Dialog>
  );
}
