import type { ReactNode } from 'react';
import RequiredMark from '@/Components/shared/RequiredMark';
import { Label } from '@/Components/ui/label';

export type FieldControlProps = {
  id: string;
  'aria-invalid': boolean;
  'aria-describedby': string | undefined;
};

type FormFieldProps = {
  id: string;
  label: string;
  error?: string;
  hint?: string;
  /** Tampilkan penanda wajib (ikon bintang) di label. */
  required?: boolean;
  children: (control: FieldControlProps) => ReactNode;
};

/** CMP-002: label, bantuan, dan galat dalam teks; menyuntikkan atribut ARIA ke kontrol. */
export default function FormField({ id, label, error, hint, required = false, children }: FormFieldProps) {
  const hintId = hint ? `${id}-hint` : undefined;
  const errorId = error ? `${id}-error` : undefined;
  const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>
        {label}
        {required ? <RequiredMark /> : null}
      </Label>
      {children({ id, 'aria-invalid': Boolean(error), 'aria-describedby': describedBy })}
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
