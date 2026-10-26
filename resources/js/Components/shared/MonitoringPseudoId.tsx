import { Fingerprint } from 'lucide-react';

/** ID semu di sel tabel atau kartu: ikon lembut dan teks monospace. Bukan identitas pengguna. */
export default function MonitoringPseudoId({ value }: { value: string }) {
  return (
    <span className="flex items-center gap-3">
      <span className="grid size-9 shrink-0 place-items-center rounded-full bg-soft-lavender text-text" aria-hidden="true">
        <Fingerprint className="size-4" />
      </span>
      <span className="font-mono font-semibold tracking-wide">{value}</span>
    </span>
  );
}
