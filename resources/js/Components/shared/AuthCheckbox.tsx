import type { ReactNode } from 'react';
import { Checkbox } from '@/Components/ui/checkbox';

type AuthCheckboxProps = {
  id: string;
  /** Boleh memuat tautan. */
  label: ReactNode;
  checked: boolean;
  onChange: (checked: boolean) => void;
  error?: string;
  required?: boolean;
};

/**
 * Kotak centang halaman tamu memakai shadcn `Checkbox` (Radix). Kotak 18px dengan area sentuh
 * diperluas lewat `after:` (target 44px tanpa membesarkan tampilan).
 */
export default function AuthCheckbox({ id, label, checked, onChange, error, required = false }: AuthCheckboxProps) {
  const errorId = error ? `${id}-error` : undefined;

  return (
    <div className="space-y-1">
      <div className="flex items-start gap-2.5 py-0.5">
        <Checkbox
          id={id}
          checked={checked}
          onCheckedChange={(value) => onChange(value === true)}
          required={required}
          aria-required={required || undefined}
          aria-invalid={Boolean(error)}
          aria-describedby={errorId}
          className="mt-[0.15rem] size-[1.125rem] cursor-pointer after:absolute after:-inset-3 after:content-[''] hover:border-primary focus-visible:border-primary focus-visible:shadow-[0_0_0_4px_var(--soft-lavender)]"
        />
        <label htmlFor={id} className="cursor-pointer">
          {label}
        </label>
      </div>
      {error ? (
        <p id={errorId} className="text-xs text-destructive">
          {error}
        </p>
      ) : null}
    </div>
  );
}
