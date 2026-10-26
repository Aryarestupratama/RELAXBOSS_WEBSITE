import type { ComponentProps } from 'react';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

/**
 * Field bergaya shadcn: Field, FieldSet, FieldLegend, FieldLabel, FieldDescription, FieldError.
 * Pengait ARIA (id, aria-describedby) tetap dipasang oleh `FormField`.
 */
function Field({ className, ...props }: ComponentProps<'div'>) {
  return <div role="group" data-slot="field" className={cn('group/field flex w-full flex-col', className)} {...props} />;
}

function FieldSet({ className, ...props }: ComponentProps<'fieldset'>) {
  return <fieldset data-slot="field-set" className={cn('flex min-w-0 flex-col gap-2', className)} {...props} />;
}

function FieldLegend({ className, ...props }: ComponentProps<'legend'>) {
  return <legend data-slot="field-legend" className={cn('mb-2 text-[0.9375rem] font-semibold text-text', className)} {...props} />;
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

export { Field, FieldSet, FieldLegend, FieldLabel, FieldDescription, FieldError };
