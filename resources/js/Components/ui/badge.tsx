import type { ComponentProps } from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

/**
 * Badge bergaya shadcn. Warna hanya dari token; teks di atas pastel selalu --text (RULE-052).
 * `crisis` memakai pasangan --crisis-bg dan --crisis-text.
 */
const badgeVariants = cva(
  'inline-flex w-fit shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap [&>svg]:size-3',
  {
    variants: {
      variant: {
        secondary: 'bg-soft-lavender text-text',
        outline: 'border border-border bg-card text-text',
        lavender: 'bg-soft-lavender text-text',
        sky: 'bg-soft-sky text-text',
        mint: 'bg-soft-mint text-text',
        peach: 'bg-soft-peach text-text',
        /* Krisis: pasangan token --crisis-bg dan --crisis-text (Design, Crisis Response). */
        crisis: 'bg-crisis-bg text-crisis-text',
      },
    },
    defaultVariants: { variant: 'secondary' },
  },
);

function Badge({ className, variant, ...props }: ComponentProps<'span'> & VariantProps<typeof badgeVariants>) {
  return <span data-slot="badge" className={cn(badgeVariants({ variant }), className)} {...props} />;
}

export { Badge, badgeVariants };
