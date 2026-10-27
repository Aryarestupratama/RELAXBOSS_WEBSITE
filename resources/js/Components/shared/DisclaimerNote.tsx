import type { ReactNode } from 'react';
import { Info } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';

/** CMP-009. `Alert` varian default sebagai catatan (`role="note"`), garis kiri `--primary`; teks "bukan diagnosis" (RULE-046). */
export default function DisclaimerNote({ children }: { children: ReactNode }) {
  return (
    <Alert role="note" className="rounded-2xl border-transparent border-l-4 border-l-primary px-4 py-3 shadow-card">
      <Info className="text-primary!" aria-hidden="true" />
      <AlertDescription className="text-[0.8125rem] leading-relaxed text-text">{children}</AlertDescription>
    </Alert>
  );
}
