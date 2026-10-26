import type { ComponentProps } from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

/**
 * Badge bergaya shadcn. Warna hanya dari token; teks di atas pastel selalu --text (RULE-052).
 */
const badgeVariants = cva(
  'inline-flex w-fit shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap text-text [&>svg]:size-3',
  {
    variants: {
      variant: {
        secondary: 'bg-neutral-soft',
        outline: 'border border-border bg-card',
        lavender: 'bg-soft-lavender',
        sky: 'bg-soft-sky',
        mint: 'bg-soft-mint',
        peach: 'bg-soft-peach',
      },
    },
    defaultVariants: { variant: 'secondary' },
  },
);

function Badge({ className, variant, ...props }: ComponentProps<'span'> & VariantProps<typeof badgeVariants>) {
  return <span data-slot="badge" className={cn(badgeVariants({ variant }), className)} {...props} />;
}

export { Badge, badgeVariants };
