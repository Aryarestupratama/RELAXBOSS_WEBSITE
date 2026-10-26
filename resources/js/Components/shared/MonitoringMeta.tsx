import type { ReactNode } from 'react';
import type { LucideIcon } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';

type MonitoringMetaProps = {
  /** Judul kartu (h2), mis. "Percakapan" beserta ID semu. */
  title: ReactNode;
  items: { icon: LucideIcon; label: string; value: ReactNode }[];
  /** Catatan kecil di bawah judul. */
  note?: string;
};

/** Ringkasan detail Monitoring: judul, catatan, dan daftar definisi (`dl`) berupa petak bernada lembut. */
export default function MonitoringMeta({ title, items, note }: MonitoringMetaProps) {
  return (
    <Card data-load className="gap-5 p-5 md:p-6">
      <CardHeader>
        <CardTitle className="text-xl font-bold tracking-tight">
          <h2>{title}</h2>
        </CardTitle>
        {note ? <CardDescription>{note}</CardDescription> : null}
      </CardHeader>
      <CardContent>
        <dl className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {items.map(({ icon: Icon, label, value }) => (
            <div key={label} className="flex items-center gap-3 rounded-2xl bg-neutral-soft px-4 py-3">
              <span
                className="grid size-9 shrink-0 place-items-center rounded-full bg-soft-lavender text-text"
                aria-hidden="true"
              >
                <Icon className="size-4" />
              </span>
              <div className="min-w-0">
                <dt className="text-xs font-medium text-text-secondary">{label}</dt>
                <dd className="font-semibold break-words text-text">{value}</dd>
              </div>
            </div>
          ))}
        </dl>
      </CardContent>
    </Card>
  );
}
