import { useEffect, useRef } from 'react';
import { Button } from '@/Components/ui/button';

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
    <dialog
      ref={ref}
      aria-labelledby="leave-title"
      aria-describedby="leave-desc"
      onCancel={(event) => {
        event.preventDefault();
        onStay();
      }}
      className="m-auto w-[calc(100%-2rem)] max-w-md rounded-xl border border-border bg-card p-6 text-card-foreground shadow-popover backdrop:bg-primary/40"
    >
      <h2 id="leave-title" className="text-lg font-semibold">
        {title}
      </h2>
      <p id="leave-desc" className="mt-2 text-text-secondary">
        {description}
      </p>
      <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <Button variant="destructive" onClick={onLeave}>
          {leaveLabel}
        </Button>
        <Button autoFocus onClick={onStay}>
          {stayLabel}
        </Button>
      </div>
    </dialog>
  );
}
