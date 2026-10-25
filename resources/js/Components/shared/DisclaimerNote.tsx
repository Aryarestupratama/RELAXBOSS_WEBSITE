import type { ReactNode } from 'react';

/** CMP-009. Garis kiri `--brand-strong`; teks "bukan diagnosis" (RULE-046). */
export default function DisclaimerNote({ children }: { children: ReactNode }) {
  return (
    <div
      role="note"
      className="rounded-2xl border-l-4 border-l-brand-strong bg-card px-4 py-3 text-[0.8125rem] leading-relaxed text-text shadow-card"
    >
      {children}
    </div>
  );
}
