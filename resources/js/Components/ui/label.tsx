import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

function Label({ className, ...props }: ComponentProps<'label'>) {
  return <label data-slot="label" className={cn('block text-sm font-medium text-text', className)} {...props} />;
}

export { Label };
