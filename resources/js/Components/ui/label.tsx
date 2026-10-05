import type { ComponentProps } from 'react';

function Label({ className = '', ...props }: ComponentProps<'label'>) {
  return (
    <label
      data-slot="label"
      className={`block text-sm font-medium text-text ${className}`}
      {...props}
    />
  );
}

export { Label };
