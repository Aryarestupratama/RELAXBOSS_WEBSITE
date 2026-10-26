import { useEffect, useRef, type FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import FormField from '@/Components/shared/FormField';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogClose, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';

type CreateAdminDialogProps = {
  open: boolean;
  onClose: () => void;
};

/**
 * Tambah admin baru (API-023, FR-035). Tanpa kata sandi: admin baru menerima tautan atur sandi lewat email.
 * Memakai elemen <dialog> bawaan, sama seperti DeleteAccountDialog.
 */
export default function CreateAdminDialog({ open, onClose }: CreateAdminDialogProps) {
  const ref = useRef<HTMLDialogElement>(null);
  const form = useForm({ name: '', email: '' });
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

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    form.post('/admin/pengguna/admin', {
      preserveScroll: true,
      onSuccess: onClose,
    });
  };

  return (
    <Dialog
      ref={ref}
      aria-labelledby="create-admin-title"
      aria-describedby="create-admin-desc"
      onCancel={(event) => {
        event.preventDefault();
        if (!form.processing) {
          onClose();
        }
      }}
    >
      <DialogHeader className="relative pr-10">
        <DialogTitle id="create-admin-title">Tambah admin</DialogTitle>
        <DialogDescription id="create-admin-desc" className="text-[0.8125rem]">
          Admin baru menerima tautan lewat email untuk memilih kata sandinya sendiri. Tautan berlaku 60 menit.
        </DialogDescription>
        <DialogClose onClick={onClose} disabled={form.processing} className="absolute top-0 right-0 mt-0 -mr-2" />
      </DialogHeader>

      <form onSubmit={submit} noValidate className="mt-4 space-y-4">
        <FormField id="admin-baru-nama" label="Nama" required error={errors.name}>
          {(control) => (
            <Input
              {...control}
              name="name"
              autoComplete="off"
              maxLength={100}
              value={form.data.name}
              onChange={(event) => form.setData('name', event.target.value)}
            />
          )}
        </FormField>
        <FormField id="admin-baru-email" label="Email" required error={errors.email}>
          {(control) => (
            <Input
              {...control}
              type="email"
              name="email"
              autoComplete="off"
              maxLength={255}
              value={form.data.email}
              onChange={(event) => form.setData('email', event.target.value)}
            />
          )}
        </FormField>

        <DialogFooter className="mt-0">
          <Button type="button" variant="ghost" className="rounded-full! px-5 text-sm" onClick={onClose} disabled={form.processing}>
            Batal
          </Button>
          <Button type="submit" className="rounded-full! px-5 text-sm" disabled={form.processing}>
            {form.processing ? 'Memproses...' : 'Buat dan kirim tautan'}
          </Button>
        </DialogFooter>
      </form>
    </Dialog>
  );
}
