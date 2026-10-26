import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * Avatar gaya shadcn, hanya `Avatar` dan `AvatarFallback` (belum ada foto profil, jadi tanpa Radix Avatar).
 * Teks di atas pastel selalu `--text` (RULE-052).
 */
function Avatar({ className, ...props }: ComponentProps<'span'>) {
  return (
    <span
      data-slot="avatar"
      className={cn('relative inline-grid size-12 shrink-0 place-items-center overflow-hidden rounded-full select-none', className)}
      {...props}
    />
  );
}

function AvatarFallback({ className, ...props }: ComponentProps<'span'>) {
  return (
    <span
      data-slot="avatar-fallback"
      className={cn('grid size-full place-items-center bg-card/60 text-xl font-bold text-text', className)}
      {...props}
    />
  );
}

export { Avatar, AvatarFallback };
