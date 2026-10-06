import type { ReactNode } from 'react';
import { Users } from 'lucide-react';
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

const cardClass = 'rounded-xl border border-border bg-card p-5 shadow-card';
const numberFormat = new Intl.NumberFormat('id-ID');

function Stat({ label, value }: { label: string; value: string }) {
  return (
    <div className={cardClass}>
      <dt className="text-sm text-text-secondary">{label}</dt>
      <dd className="mt-1 text-3xl font-semibold">{value}</dd>
    </div>
  );
}

function Section({ id, title, children }: { id: string; title: string; children: ReactNode }) {
  return (
    <section aria-labelledby={id} className="mt-8">
      <h2 id={id} className="text-xl font-semibold">
        {title}
      </h2>
      <div className="mt-4">{children}</div>
    </section>
  );
}

function AiTable({ caption, rows }: { caption: string; rows: AiRow[] }) {
  return (
    <div className="overflow-x-auto rounded-xl border border-border bg-card">
      <table className="w-full min-w-[32rem] text-left">
        <caption className="px-4 py-3 text-left font-medium">{caption}</caption>
        <thead className="border-y border-border text-sm text-text-secondary">
          <tr>
            <th scope="col" className="px-4 py-2 font-medium">
              Fitur
            </th>
            <th scope="col" className="px-4 py-2 text-right font-medium">
              Request
            </th>
            <th scope="col" className="px-4 py-2 text-right font-medium">
              Dibatasi (429)
            </th>
            <th scope="col" className="px-4 py-2 text-right font-medium">
              Galat
            </th>
            <th scope="col" className="px-4 py-2 text-right font-medium">
              Token
            </th>
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.feature} className="border-b border-border last:border-b-0">
              <th scope="row" className="px-4 py-2 font-medium">
                {row.feature}
              </th>
              <td className="px-4 py-2 text-right">{numberFormat.format(row.requests)}</td>
              <td className="px-4 py-2 text-right">{numberFormat.format(row.rate_limited)}</td>
              <td className="px-4 py-2 text-right">{numberFormat.format(row.errors)}</td>
              <td className="px-4 py-2 text-right">{numberFormat.format(row.tokens)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

/** SCR-019 */
export default function Dashboard({ stats, min_group_size }: DashboardProps) {
  const { users, usage, crisis, kpi, ai } = stats;

  return (
    <AdminShell title="Ringkasan">
      <h1>Ringkasan</h1>
      <p className="mt-2 text-text-secondary">
        Angka agregat tanpa identitas. Angka di bawah {min_group_size} ditampilkan sebagai &quot;&lt;{min_group_size}
        &quot; demi privasi pengguna. Akun admin tidak dihitung.
      </p>

      {users.empty ? (
        <div className="mt-6">
          <EmptyState icon={Users} message="Belum ada pengguna. Angka akan muncul setelah ada yang mendaftar." />
        </div>
      ) : null}

      <Section id="pengguna" title="Pengguna">
        <dl className="grid gap-4 sm:grid-cols-2">
          <Stat label="Akun terdaftar" value={users.registered} />
          <Stat label="Akun terverifikasi" value={users.verified} />
        </dl>
      </Section>

      <Section id="pemakaian" title="Pemakaian fitur">
        <dl className="grid gap-4 sm:grid-cols-3">
          <Stat label="Asesmen selesai" value={usage.assessments_total} />
          <Stat label="Mood Entry dibuat" value={usage.mood_entries} />
          <Stat label="Percakapan dimulai" value={usage.conversations} />
        </dl>

        <div className="mt-4 overflow-x-auto rounded-xl border border-border bg-card">
          <table className="w-full min-w-[20rem] text-left">
            <caption className="px-4 py-3 text-left font-medium">Asesmen selesai per jenis</caption>
            <thead className="border-y border-border text-sm text-text-secondary">
              <tr>
                <th scope="col" className="px-4 py-2 font-medium">
                  Asesmen
                </th>
                <th scope="col" className="px-4 py-2 text-right font-medium">
                  Selesai
                </th>
              </tr>
            </thead>
            <tbody>
              {usage.assessments_by_type.length === 0 ? (
                <tr>
                  <td colSpan={2} className="px-4 py-4 text-text-secondary">
                    Belum ada Asesmen yang selesai.
                  </td>
                </tr>
              ) : (
                usage.assessments_by_type.map((row) => (
                  <tr key={row.name} className="border-b border-border last:border-b-0">
                    <th scope="row" className="px-4 py-2 font-medium">
                      {row.name}
                    </th>
                    <td className="px-4 py-2 text-right">{row.total}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </Section>

      <Section id="kpi" title="Indikator keberhasilan">
        <ul className="grid gap-4 md:grid-cols-3">
          {kpi.map((item) => (
            <li key={item.code} className={cardClass}>
              <h3 className="text-base font-semibold">
                {item.code} {item.title}
              </h3>
              <p className="mt-2 text-3xl font-semibold">
                {item.value ?? 'Belum cukup data'}
              </p>
              <p className="mt-1 text-sm text-text-secondary">
                Target {item.target}. Kohort: {item.cohort} orang.
              </p>
              <p className="mt-2 text-sm text-text-secondary">{item.description}</p>
              {item.value === null ? (
                <p className="mt-2 text-sm text-text-secondary">
                  Persentase tampil bila kohort minimal {min_group_size} orang.
                </p>
              ) : null}
            </li>
          ))}
        </ul>
      </Section>

      <Section id="krisis" title="Crisis Response terpicu">
        <p className="text-text-secondary">Hanya hitungan, tanpa isi pesan dan tanpa identitas.</p>
        <dl className="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Stat label="Total" value={crisis.total} />
          <Stat label="30 hari terakhir" value={crisis.last_30_days} />
          <Stat label="Dari chat" value={crisis.rule} />
          <Stat label="Dari jawaban PFA" value={crisis.pfa_rule} />
        </dl>
      </Section>

      <Section id="ai" title="Pemakaian AI">
        <p className="text-text-secondary">Hari dihitung dalam UTC. Token adalah jumlah token prompt dan jawaban.</p>
        <div className="mt-3 grid gap-4">
          <AiTable caption="Hari ini" rows={ai.today} />
          <AiTable caption="30 hari terakhir" rows={ai.last_30_days} />
        </div>
      </Section>
    </AdminShell>
  );
}
