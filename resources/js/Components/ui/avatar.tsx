import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

/**
 * Avatar gaya shadcn: `Avatar`, `AvatarImage` (foto profil same-origin, FR-033), dan `AvatarFallback` (inisial).
 * Tanpa Radix Avatar; <img> biasa menutupi fallback.
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

function AvatarImage({ className, alt = '', ...props }: ComponentProps<'img'>) {
  return (
    <img
      data-slot="avatar-image"
      alt={alt}
      className={cn('absolute inset-0 size-full object-cover', className)}
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

export { Avatar, AvatarFallback, AvatarImage };
