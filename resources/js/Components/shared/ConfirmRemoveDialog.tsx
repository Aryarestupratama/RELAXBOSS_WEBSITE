import { useEffect, useId, useRef } from 'react';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';

type ConfirmRemoveDialogProps = {
  open: boolean;
  title: string;
  description: string;
  confirmLabel: string;
  onCancel: () => void;
  onConfirm: () => void;
};

/**
 * Konfirmasi sebelum menghapus baris formulir yang sudah berisi (butir, aturan). Memakai `Dialog` bawaan
 * (`<dialog>`, aman untuk CSP). Fokus awal di "Batal" agar tidak terhapus tanpa sengaja.
 */
export default function ConfirmRemoveDialog({
  open,
  title,
  description,
  confirmLabel,
  onCancel,
  onConfirm,
}: ConfirmRemoveDialogProps) {
  const ref = useRef<HTMLDialogElement>(null);
  const uid = useId();

  useEffect(() => {
    const dialog = ref.current;
    if (!dialog) {
      return;
    }
    if (open && !dialog.open) {
      dialog.showModal();
    }
    if (!open && dialog.open) {
      dialog.close();
    }
  }, [open]);

  return (
    <Dialog
      ref={ref}
      aria-labelledby={`${uid}-title`}
      aria-describedby={`${uid}-desc`}
      onCancel={(event) => {
        event.preventDefault();
        onCancel();
      }}
    >
      <DialogHeader>
        <DialogTitle id={`${uid}-title`}>{title}</DialogTitle>
        <DialogDescription id={`${uid}-desc`}>{description}</DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button type="button" variant="destructive" className="rounded-full! px-5 text-sm" onClick={onConfirm}>
          {confirmLabel}
        </Button>
        <Button type="button" autoFocus className="rounded-full! px-5 text-sm" onClick={onCancel}>
          Batal
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
