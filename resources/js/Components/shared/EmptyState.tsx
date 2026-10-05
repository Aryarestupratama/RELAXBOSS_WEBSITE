import type { ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';

type EmptyStateProps = {
  icon: LucideIcon;
  message: string;
  action?: ReactNode;
};

/** CMP-010. Ikon, pesan hangat, satu aksi. */
export default function EmptyState({ icon: Icon, message, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center gap-4 rounded-xl border border-border bg-card px-6 py-10 text-center">
      <span className="flex size-12 items-center justify-center rounded-full bg-neutral-soft text-brand-strong">
        <Icon className="size-6" aria-hidden="true" />
      </span>
      <p className="max-w-sm text-text-secondary">{message}</p>
      {action}
    </div>
  );
}
