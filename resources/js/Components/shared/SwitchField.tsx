import { Switch } from '@/Components/ui/switch';
import { cn } from '@/lib/utils';

type SwitchFieldProps = {
  id: string;
  label: string;
  checked: boolean;
  onChange: (checked: boolean) => void;
  hint?: string;
  error?: string;
  className?: string;
};

/**
 * Pengganti `CheckboxField` untuk pilihan ya atau tidak di formulir Admin: baris putih berbingkai berisi label,
 * bantuan, dan `Switch`. Label menekan switch. Galat ada di luar kotak (di atas `--card`) agar kontrasnya cukup.
 */
export default function SwitchField({ id, label, checked, onChange, hint, error, className }: SwitchFieldProps) {
  const hintId = hint ? `${id}-hint` : undefined;
  const errorId = error ? `${id}-error` : undefined;
  const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

  return (
    <div className={cn('space-y-1.5', className)}>
      <div className="flex min-h-14 items-center justify-between gap-4 rounded-2xl border border-border/60 bg-card px-4 py-3">
        <div className="min-w-0">
          <label htmlFor={id} className="block cursor-pointer text-sm leading-snug font-medium">
            {label}
          </label>
          {hint ? (
            <p id={hintId} className="mt-0.5 text-[0.8125rem] leading-snug text-text-secondary">
              {hint}
            </p>
          ) : null}
        </div>
        <Switch
          id={id}
          checked={checked}
          onCheckedChange={onChange}
          aria-invalid={Boolean(error)}
          aria-describedby={describedBy}
        />
      </div>
      {error ? (
        <p id={errorId} className="text-sm text-destructive">
          {error}
        </p>
      ) : null}
    </div>
  );
}
