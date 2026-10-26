import { useEffect, useRef } from 'react';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';

type ConfirmLeaveDialogProps = {
  open: boolean;
  title: string;
  description: string;
  stayLabel: string;
  leaveLabel: string;
  onStay: () => void;
  onLeave: () => void;
};

/**
 * Dialog konfirmasi memakai elemen <dialog> bawaan browser: fokus terkunci dan Esc menutup
 * tanpa menyuntik <style> (aman untuk CSP bernonce).
 */
export default function ConfirmLeaveDialog({
  open,
  title,
  description,
  stayLabel,
  leaveLabel,
  onStay,
  onLeave,
}: ConfirmLeaveDialogProps) {
  const ref = useRef<HTMLDialogElement>(null);

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
      aria-labelledby="leave-title"
      aria-describedby="leave-desc"
      onCancel={(event) => {
        event.preventDefault();
        onStay();
      }}
    >
      <DialogHeader>
        <DialogTitle id="leave-title">{title}</DialogTitle>
        <DialogDescription id="leave-desc">{description}</DialogDescription>
      </DialogHeader>
      <DialogFooter>
        <Button variant="destructive" className="rounded-full! px-5 text-sm" onClick={onLeave}>
          {leaveLabel}
        </Button>
        <Button autoFocus className="rounded-full! px-5 text-sm" onClick={onStay}>
          {stayLabel}
        </Button>
      </DialogFooter>
    </Dialog>
  );
}
