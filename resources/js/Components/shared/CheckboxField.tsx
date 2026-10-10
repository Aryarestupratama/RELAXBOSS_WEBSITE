import type { ReactNode } from 'react';

type CheckboxFieldProps = {
  id: string;
  /** Boleh memuat tautan. */
  label: ReactNode;
  checked: boolean;
  onChange: (checked: boolean) => void;
  hint?: string;
  error?: string;
  required?: boolean;
};

/** Kotak centang dengan label yang bisa ditekan; target sentuh minimal 44px. */
export default function CheckboxField({ id, label, checked, onChange, hint, error, required = false }: CheckboxFieldProps) {
  const hintId = hint ? `${id}-hint` : undefined;
  const errorId = error ? `${id}-error` : undefined;
  const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

  return (
    <div className="space-y-1">
      <div className="flex min-h-11 items-center gap-3">
        <input
          id={id}
          type="checkbox"
          checked={checked}
          onChange={(event) => onChange(event.target.checked)}
          required={required}
          aria-required={required || undefined}
          aria-invalid={Boolean(error)}
          aria-describedby={describedBy}
          className="size-5 shrink-0 accent-primary"
        />
        <label htmlFor={id} className="cursor-pointer">
          {label}
        </label>
      </div>
      {hint ? (
        <p id={hintId} className="text-sm text-text-secondary">
          {hint}
        </p>
      ) : null}
      {error ? (
        <p id={errorId} className="text-sm text-destructive">
          {error}
        </p>
      ) : null}
    </div>
  );
}
