import type { ComponentProps } from 'react';

/**
 * Textarea RelaxBoss: mengikuti Input (tinggi min. 44px, cincin fokus dari app.css).
 * Galat ditandai lewat aria-invalid (diatur FormField).
 */
function Textarea({ className = '', ...props }: ComponentProps<'textarea'>) {
  return (
    <textarea
      data-slot="textarea"
      className={`min-h-24 w-full resize-y rounded-lg border-[1.5px] border-input bg-card px-3.5 py-2.5 text-base text-foreground placeholder:text-text-muted disabled:cursor-not-allowed disabled:opacity-50 aria-invalid:border-destructive ${className}`}
      {...props}
    />
  );
}

export { Textarea };
