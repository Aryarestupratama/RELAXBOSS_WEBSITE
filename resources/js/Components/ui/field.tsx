import type { ComponentProps } from 'react';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

/**
 * Field bergaya shadcn: Field, FieldLabel, FieldDescription, FieldError.
 * Pengait ARIA (id, aria-describedby) tetap dipasang oleh `FormField`.
 */
function Field({ className, ...props }: ComponentProps<'div'>) {
  return <div role="group" data-slot="field" className={cn('group/field flex w-full flex-col', className)} {...props} />;
}

function FieldLabel({ className, ...props }: ComponentProps<typeof Label>) {
  return <Label data-slot="field-label" className={cn('w-fit', className)} {...props} />;
}

function FieldDescription({ className, ...props }: ComponentProps<'p'>) {
  return <p data-slot="field-description" className={cn('text-sm text-text-secondary', className)} {...props} />;
}

function FieldError({ className, ...props }: ComponentProps<'p'>) {
  return <p data-slot="field-error" className={cn('text-sm text-destructive', className)} {...props} />;
}

export { Field, FieldLabel, FieldDescription, FieldError };
