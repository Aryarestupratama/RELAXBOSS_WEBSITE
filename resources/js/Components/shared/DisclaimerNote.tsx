import type { ReactNode } from 'react';

/** CMP-009. Garis kiri `--brand-strong`; teks "bukan diagnosis" (RULE-046). */
export default function DisclaimerNote({ children }: { children: ReactNode }) {
  return (
    <div
      role="note"
      className="rounded-lg border border-l-4 border-border border-l-brand-strong bg-card p-4 text-sm text-text"
    >
      {children}
    </div>
  );
}
