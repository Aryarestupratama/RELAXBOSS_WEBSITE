import type { ReactNode } from 'react';
import RequiredMark from '@/Components/shared/RequiredMark';
import { Field, FieldDescription, FieldError, FieldLabel } from '@/Components/ui/field';

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
  /** Varian ringkas untuk halaman tamu (Masuk, Daftar): label dan bantuan lebih kecil, jarak lebih rapat. */
  compact?: boolean;
  children: (control: FieldControlProps) => ReactNode;
};

/** CMP-002: label, bantuan, dan galat dalam teks; menyuntikkan atribut ARIA ke kontrol. */
export default function FormField({ id, label, error, hint, required = false, compact = false, children }: FormFieldProps) {
  const hintId = hint ? `${id}-hint` : undefined;
  const errorId = error ? `${id}-error` : undefined;
  const describedBy = [hintId, errorId].filter(Boolean).join(' ') || undefined;

  return (
    <Field className={compact ? 'gap-1' : 'gap-1.5'}>
      <FieldLabel htmlFor={id} className={compact ? 'text-[0.8125rem]' : undefined}>
        {label}
        {required ? <RequiredMark /> : null}
      </FieldLabel>
      {children({ id, 'aria-invalid': Boolean(error), 'aria-describedby': describedBy })}
      {hint ? (
        <FieldDescription id={hintId} className={compact ? 'text-xs leading-snug' : undefined}>
          {hint}
        </FieldDescription>
      ) : null}
      {error ? (
        <FieldError id={errorId} className={compact ? 'text-xs leading-snug' : undefined}>
          {error}
        </FieldError>
      ) : null}
    </Field>
  );
}
