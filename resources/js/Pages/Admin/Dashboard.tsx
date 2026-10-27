import type { ReactNode } from 'react';
import {
  CalendarDays,
  ClipboardCheck,
  ClipboardList,
  LifeBuoy,
  MessageCircle,
  Smile,
  UserCheck,
  Users,
} from 'lucide-react';
import AdminShell from '@/Layouts/AdminShell';
import InlineNote from '@/Components/shared/InlineNote';
import PageHeader from '@/Components/shared/PageHeader';
import StatCard from '@/Components/shared/StatCard';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

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

const numberFormat = new Intl.NumberFormat('id-ID');

/** Judul kolom angka: rata kanan. */
const NUM_HEAD = 'text-right';
/** Sel judul baris: `th scope="row"` dengan tampilan sel biasa. */
const ROW_HEAD = 'h-auto px-3 py-3 text-sm font-normal tracking-normal whitespace-normal text-text';

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
    <section aria-labelledby={id} data-load className="flex flex-col gap-3">
      <div>
        <h2 id={id} className="text-lg leading-snug font-bold tracking-tight">
          {title}
        </h2>
        {note ? <p className="mt-0.5 text-sm text-text-secondary">{note}</p> : null}
      </div>
      {children}
    </section>
  );
}

function AiTable({ caption, rows }: { caption: string; rows: AiRow[] }) {
  return (
    <Card className="gap-3 p-5">
      <CardHeader>
        <CardTitle className="text-base font-bold">{caption}</CardTitle>
      </CardHeader>
      <CardContent>
        <Table className="min-w-[28rem]">
          <TableCaption className="sr-only">{caption}</TableCaption>
          <TableHeader>
            <TableRow className="hover:bg-transparent">
              <TableHead>Fitur</TableHead>
              <TableHead className={NUM_HEAD}>Request</TableHead>
              <TableHead className={NUM_HEAD}>Dibatasi (429)</TableHead>
              <TableHead className={NUM_HEAD}>Galat</TableHead>
              <TableHead className={NUM_HEAD}>Token</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {rows.map((row) => (
              <TableRow key={row.feature}>
                <TableHead scope="row" className={ROW_HEAD}>
                  {row.feature}
                </TableHead>
                <TableCell className="text-right tabular-nums">{numberFormat.format(row.requests)}</TableCell>
                <TableCell className="text-right tabular-nums">{numberFormat.format(row.rate_limited)}</TableCell>
                <TableCell className="text-right tabular-nums">{numberFormat.format(row.errors)}</TableCell>
                <TableCell className="text-right font-semibold tabular-nums">{numberFormat.format(row.tokens)}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
}

/** SCR-019 */
export default function Dashboard({ stats, min_group_size }: DashboardProps) {
  const { users, usage, crisis, kpi, ai } = stats;

  return (
    <AdminShell title="Ringkasan" staggered>
      <PageHeader title="Ringkasan" description="Angka agregat tanpa identitas." />

      <div className="flex flex-col gap-7">
        <div data-load>
          <InlineNote>Angka di bawah {min_group_size} tampil sebagai &quot;&lt;{min_group_size}&quot; demi privasi, dan akun admin tidak dihitung.</InlineNote>
        </div>

        {users.empty ? (
          <Card tone="lavender" data-load className="items-center gap-3 px-6 py-8 text-center">
            <span className="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <Users className="size-6" />
            </span>
            <p className="max-w-sm text-sm text-text">
              Belum ada pengguna. Angka akan muncul setelah ada yang mendaftar.
            </p>
          </Card>
        ) : null}

        <Section id="pengguna" title="Pengguna">
          <dl className="grid gap-4 sm:grid-cols-2">
            <StatCard label="Akun terdaftar" value={users.registered} icon={Users} tone="lavender" />
            <StatCard label="Akun terverifikasi" value={users.verified} icon={UserCheck} tone="sky" />
          </dl>
        </Section>

        <Section id="pemakaian" title="Pemakaian fitur">
          <div className="grid items-start gap-4 lg:grid-cols-12">
            <dl className="grid gap-4 sm:grid-cols-3 lg:col-span-5 lg:grid-cols-1">
              <StatCard label="Asesmen selesai" value={usage.assessments_total} icon={ClipboardCheck} tone="peach" />
              <StatCard label="Mood Entry dibuat" value={usage.mood_entries} icon={Smile} tone="mint" />
              <StatCard label="Percakapan dimulai" value={usage.conversations} icon={MessageCircle} tone="lavender" />
            </dl>

            <Card className="gap-3 p-5 lg:col-span-7">
              <CardHeader>
                <CardTitle className="text-base font-bold">Asesmen selesai per jenis</CardTitle>
              </CardHeader>
              <CardContent>
                {usage.assessments_by_type.length === 0 ? (
                  <div className="flex flex-col items-center gap-2 rounded-2xl border border-border/60 bg-card px-6 py-8 text-center">
                    <ClipboardList className="size-8 text-text-muted" aria-hidden="true" />
                    <p className="text-sm text-text-secondary">Belum ada Asesmen yang selesai.</p>
                  </div>
                ) : (
                  <Table className="min-w-[18rem]">
                    <TableCaption className="sr-only">Asesmen selesai per jenis</TableCaption>
                    <TableHeader>
                      <TableRow className="hover:bg-transparent">
                        <TableHead>Asesmen</TableHead>
                        <TableHead className={NUM_HEAD}>Selesai</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {usage.assessments_by_type.map((row) => (
                        <TableRow key={row.name}>
                          <TableHead scope="row" className={ROW_HEAD}>
                            {row.name}
                          </TableHead>
                          <TableCell className="text-right font-semibold tabular-nums">{row.total}</TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                )}
              </CardContent>
            </Card>
          </div>
        </Section>

        <Section id="kpi" title="Indikator keberhasilan">
          <ul className="grid gap-4 md:grid-cols-3">
            {kpi.map((item) => (
              <li key={item.code} className="flex">
                <Card className="w-full gap-4 p-5">
                  <CardHeader className="gap-2">
                    <Badge variant="outline" className="w-fit">
                      {item.code}
                    </Badge>
                    <CardTitle className="text-base font-bold">{item.title}</CardTitle>
                  </CardHeader>
                  <CardContent className="flex flex-col gap-2">
                    {item.value === null ? (
                      <p className="text-lg font-semibold text-text-muted">Belum cukup data</p>
                    ) : (
                      <p className="text-3xl leading-none font-bold tracking-tight tabular-nums">{item.value}</p>
                    )}
                    {item.value === null ? (
                      <CardDescription>Persentase tampil bila kohort minimal {min_group_size} orang.</CardDescription>
                    ) : (
                      <div className="flex flex-wrap gap-1.5">
                        <Badge variant="mint">Target {item.target}</Badge>
                        <Badge variant="secondary">Kohort: {item.cohort} orang</Badge>
                      </div>
                    )}
                  </CardContent>
                  <p className="mt-auto rounded-2xl border border-border/60 bg-card p-3 text-[0.8125rem] leading-relaxed text-text-secondary">
                    {item.description}
                  </p>
                </Card>
              </li>
            ))}
          </ul>
        </Section>

        <Section id="krisis" title="Crisis Response terpicu" note="Hanya hitungan, tanpa isi pesan dan tanpa identitas.">
          <dl className="grid grid-cols-2 gap-4 md:grid-cols-4">
            <StatCard tone="crisis" label="Total" value={crisis.total} icon={LifeBuoy} />
            <StatCard tone="crisis" label="30 hari terakhir" value={crisis.last_30_days} icon={CalendarDays} />
            <StatCard tone="crisis" label="Dari chat" value={crisis.rule} icon={MessageCircle} />
            <StatCard tone="crisis" label="Dari jawaban PFA" value={crisis.pfa_rule} icon={ClipboardList} />
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
