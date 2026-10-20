import type { ReactNode } from 'react';
import { ClipboardList, Users } from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import EmptyState from '@/Components/shared/EmptyState';

type AiRow = {
  feature: string;
  requests: number;
  rate_limited: number;
  errors: number;
  tokens: number;
};

type Kpi = {
  code: string;
  title: string;
  description: string;
  target: string;
  value: string | null;
  cohort: string;
};

type Stats = {
  users: { registered: string; verified: string; empty: boolean };
  usage: {
    assessments_total: string;
    assessments_by_type: { name: string; total: string }[];
    mood_entries: string;
    conversations: string;
  };
  crisis: { total: string; last_30_days: string; rule: string; pfa_rule: string };
  kpi: Kpi[];
  ai: { today: AiRow[]; last_30_days: AiRow[] };
};

type DashboardProps = {
  stats: Stats;
  min_group_size: number;
};

const cardClass = 'rounded-xl bg-card p-5 shadow-card';
const crisisCardClass = 'rounded-xl bg-crisis-bg p-5';
const numberFormat = new Intl.NumberFormat('id-ID');

function Stat({ label, value, crisis = false }: { label: string; value: string; crisis?: boolean }) {
  return (
    <div className={`flex flex-col justify-between ${crisis ? crisisCardClass : cardClass}`}>
      <dt className={`text-sm font-medium ${crisis ? 'text-crisis-text' : 'text-text-secondary'}`}>{label}</dt>
      <dd className={`mt-3 text-3xl font-semibold ${crisis ? 'text-crisis-text' : ''}`}>{value}</dd>
    </div>
  );
}

function Section({
  id,
  title,
  note,
  children,
}: {
  id: string;
  title: string;
  note?: string;
  children: ReactNode;
}) {
  return (
    <section aria-labelledby={id} className="flex flex-col gap-3">
      <div>
        <h2 id={id}>{title}</h2>
        {note ? <p className="text-sm text-text-secondary">{note}</p> : null}
      </div>
      {children}
    </section>
  );
}

function AiTable({ caption, rows }: { caption: string; rows: AiRow[] }) {
  return (
    <div className={cardClass}>
      <h3 className="mb-3">{caption}</h3>
      <div className="overflow-x-auto">
        <table className="w-full min-w-[28rem] text-left text-sm">
          <caption className="sr-only">{caption}</caption>
          <thead className="bg-neutral-soft text-text-secondary">
            <tr>
              <th scope="col" className="rounded-l-lg px-3 py-2.5 font-medium">
                Fitur
              </th>
              <th scope="col" className="px-2 py-2.5 text-right font-medium">
                Request
              </th>
              <th scope="col" className="px-2 py-2.5 text-right font-medium">
                Dibatasi (429)
              </th>
              <th scope="col" className="px-2 py-2.5 text-right font-medium">
                Galat
              </th>
              <th scope="col" className="rounded-r-lg px-3 py-2.5 text-right font-medium">
                Token
              </th>
            </tr>
          </thead>
          <tbody>
            {rows.map((row) => (
              <tr key={row.feature} className="border-b border-border last:border-b-0">
                <th scope="row" className="px-3 py-3 font-normal">
                  {row.feature}
                </th>
                <td className="px-2 py-3 text-right">{numberFormat.format(row.requests)}</td>
                <td className="px-2 py-3 text-right">{numberFormat.format(row.rate_limited)}</td>
                <td className="px-2 py-3 text-right">{numberFormat.format(row.errors)}</td>
                <td className="px-3 py-3 text-right font-medium">{numberFormat.format(row.tokens)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

/** SCR-019 */
export default function Dashboard({ stats, min_group_size }: DashboardProps) {
  const { users, usage, crisis, kpi, ai } = stats;

  return (
    <AdminShell title="Ringkasan">
      <div className="flex flex-col gap-8">
        <header className="max-w-3xl">
          <h1>Ringkasan</h1>
          <p className="mt-2 text-text-secondary">
            Angka agregat tanpa identitas. Angka di bawah {min_group_size} ditampilkan sebagai &quot;&lt;
            {min_group_size}&quot; demi privasi pengguna. Akun admin tidak dihitung.
          </p>
        </header>

        {users.empty ? (
          <EmptyState icon={Users} message="Belum ada pengguna. Angka akan muncul setelah ada yang mendaftar." />
        ) : null}

        <Section id="pengguna" title="Pengguna">
          <dl className="grid gap-4 sm:grid-cols-2">
            <Stat label="Akun terdaftar" value={users.registered} />
            <Stat label="Akun terverifikasi" value={users.verified} />
          </dl>
        </Section>

        <Section id="pemakaian" title="Pemakaian fitur">
          <div className="grid items-start gap-4 lg:grid-cols-12">
            <dl className="flex flex-col gap-4 lg:col-span-5">
              <Stat label="Asesmen selesai" value={usage.assessments_total} />
              <Stat label="Mood Entry dibuat" value={usage.mood_entries} />
              <Stat label="Percakapan dimulai" value={usage.conversations} />
            </dl>

            <div className={`${cardClass} lg:col-span-7`}>
              <h3 className="mb-3">Asesmen selesai per jenis</h3>
              {usage.assessments_by_type.length === 0 ? (
                <div className="flex flex-col items-center gap-2 rounded-lg bg-neutral-soft px-6 py-8 text-center">
                  <ClipboardList className="size-8 text-text-muted" aria-hidden="true" />
                  <p className="text-text-secondary">Belum ada Asesmen yang selesai.</p>
                </div>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full min-w-[18rem] text-left text-sm">
                    <caption className="sr-only">Asesmen selesai per jenis</caption>
                    <thead className="bg-neutral-soft text-text-secondary">
                      <tr>
                        <th scope="col" className="rounded-l-lg px-3 py-2.5 font-medium">
                          Asesmen
                        </th>
                        <th scope="col" className="rounded-r-lg px-3 py-2.5 text-right font-medium">
                          Selesai
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {usage.assessments_by_type.map((row) => (
                        <tr key={row.name} className="border-b border-border last:border-b-0">
                          <th scope="row" className="px-3 py-3 font-normal">
                            {row.name}
                          </th>
                          <td className="px-3 py-3 text-right font-medium">{row.total}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        </Section>

        <Section id="kpi" title="Indikator keberhasilan">
          <ul className="grid gap-4 md:grid-cols-3">
            {kpi.map((item) => (
              <li key={item.code} className={`flex flex-col justify-between ${cardClass}`}>
                <div>
                  <h3 className="text-base font-semibold">
                    {item.code} {item.title}
                  </h3>
                  {item.value === null ? (
                    <p className="mt-3 text-lg font-semibold text-text-muted">Belum cukup data</p>
                  ) : (
                    <p className="mt-3 text-3xl font-semibold">{item.value}</p>
                  )}
                  <p className="mt-1 text-sm text-text-secondary">
                    {item.value === null
                      ? `Persentase tampil bila kohort minimal ${min_group_size} orang.`
                      : `Target ${item.target}. Kohort: ${item.cohort} orang.`}
                  </p>
                </div>
                <p className="mt-4 rounded-lg bg-neutral-soft p-3 text-sm text-text-secondary">{item.description}</p>
              </li>
            ))}
          </ul>
        </Section>

        <Section id="krisis" title="Crisis Response terpicu" note="Hanya hitungan, tanpa isi pesan dan tanpa identitas.">
          <dl className="grid grid-cols-2 gap-4 md:grid-cols-4">
            <Stat crisis label="Total" value={crisis.total} />
            <Stat crisis label="30 hari terakhir" value={crisis.last_30_days} />
            <Stat crisis label="Dari chat" value={crisis.rule} />
            <Stat crisis label="Dari jawaban PFA" value={crisis.pfa_rule} />
          </dl>
        </Section>

        <Section
          id="ai"
          title="Pemakaian AI"
          note="Hari dihitung dalam UTC. Token adalah jumlah token prompt dan jawaban."
        >
          <div className="grid gap-4 lg:grid-cols-2">
            <AiTable caption="Hari ini" rows={ai.today} />
            <AiTable caption="30 hari terakhir" rows={ai.last_30_days} />
          </div>
        </Section>
      </div>
    </AdminShell>
  );
}
