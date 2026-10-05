import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, ClipboardCheck, MessageCircle, Smile, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import EmptyState from '@/Components/shared/EmptyState';
import { Button } from '@/Components/ui/button';

type LastMood = {
  mood: number;
  mood_label: string;
  arousal_label: string | null;
  when: string;
};

type LastAttempt = {
  id: number;
  assessment_name: string;
  completed_at: string;
  sub_scales: { name: string; interpretation: string; severity_level: string }[];
};

type DashboardProps = {
  first_name: string;
  last_mood: LastMood | null;
  last_attempt: LastAttempt | null;
};

type FeatureCardProps = {
  icon: LucideIcon;
  title: string;
  description: string;
  className: string;
  href?: string;
  note?: string;
};

const cardBase = 'flex min-h-36 flex-col gap-2 rounded-xl p-5 shadow-card';

/** Kartu fitur. Tanpa `href` = belum tersedia (bukan tautan, agar tidak menuju 404). */
function FeatureCard({ icon: Icon, title, description, className, href, note }: FeatureCardProps) {
  const body = (
    <>
      <Icon className="size-6" aria-hidden="true" />
      <h2 className="text-lg font-semibold">{title}</h2>
      <p className="text-sm">{description}</p>
      {note ? <p className="mt-auto text-sm font-medium">{note}</p> : null}
    </>
  );

  if (!href) {
    return <div className={`${cardBase} ${className}`}>{body}</div>;
  }

  return (
    <Link
      href={href}
      className={`${cardBase} ${className} transition-opacity hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2`}
    >
      {body}
    </Link>
  );
}

function SectionCard({ title, children }: { title: string; children: ReactNode }) {
  return (
    <section className="rounded-xl border border-border bg-card p-5 shadow-card">
      <h2 className="text-lg font-semibold">{title}</h2>
      <div className="mt-4">{children}</div>
    </section>
  );
}

/** SCR-011 */
export default function Dashboard({ first_name, last_mood, last_attempt }: DashboardProps) {
  return (
    <AppShell title="Dashboard">
      <h1>Halo, {first_name}.</h1>
      <p className="mt-2 text-text-secondary">Apa yang ingin kamu lakukan hari ini?</p>

      <div className="mt-6 grid gap-4 md:grid-cols-3">
        <FeatureCard
          icon={ClipboardCheck}
          title="Asesmen"
          description="Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu."
          className="bg-mood-yellow text-text"
          href="/app/asesmen"
        />
        <FeatureCard
          icon={Smile}
          title="Mood Tracker"
          description="Catat perasaanmu dan lihat polanya dalam seminggu."
          className="bg-mood-green text-text"
          href="/app/mood"
        />
        {/* RelaxMate belum ada (TASK-020): tampil sebagai kartu biasa, bukan tautan. */}
        <FeatureCard
          icon={MessageCircle}
          title="RelaxMate"
          description="Teman bicara berbasis AI. Bukan psikolog atau layanan medis."
          className="bg-brand text-on-primary"
          note="Segera hadir"
        />
      </div>

      <div className="mt-6 grid gap-4 md:grid-cols-2">
        <SectionCard title="Mood terakhir">
          {last_mood ? (
            <div className="space-y-4">
              <p className="font-medium">
                {last_mood.mood_label}
                {last_mood.arousal_label ? (
                  <span className="font-normal text-text-secondary"> · {last_mood.arousal_label}</span>
                ) : null}
              </p>
              <p className="text-sm text-text-secondary">{last_mood.when}</p>
              <Button asChild variant="outline">
                <Link href="/app/mood">Catat mood</Link>
              </Button>
            </div>
          ) : (
            <EmptyState
              icon={Smile}
              message="Belum ada catatan mood. Bagaimana perasaanmu hari ini?"
              action={
                <Button asChild>
                  <Link href="/app/mood">Catat mood</Link>
                </Button>
              }
            />
          )}
        </SectionCard>

        <SectionCard title="Hasil asesmen terakhir">
          {last_attempt ? (
            <div className="space-y-4">
              <Link
                href={`/app/riwayat/${last_attempt.id}`}
                className="flex min-h-11 items-center justify-between gap-3 rounded-lg border border-border bg-background p-4 transition-colors hover:bg-neutral-soft"
              >
                <span className="min-w-0">
                  <span className="block font-semibold">{last_attempt.assessment_name}</span>
                  <span className="block text-sm text-text-secondary">{last_attempt.completed_at}</span>
                  <span className="mt-2 flex flex-wrap gap-2">
                    {last_attempt.sub_scales.map((item) => (
                      <span key={item.name} className="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text">
                        {item.name}: {item.interpretation}
                      </span>
                    ))}
                  </span>
                </span>
                <ChevronRight className="size-5 shrink-0 text-text-secondary" aria-hidden="true" />
              </Link>
              <Button asChild variant="outline">
                <Link href="/app/riwayat">Lihat riwayat</Link>
              </Button>
            </div>
          ) : (
            <EmptyState
              icon={ClipboardCheck}
              message="Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu."
              action={
                <Button asChild>
                  <Link href="/app/asesmen">Mulai asesmen</Link>
                </Button>
              }
            />
          )}
        </SectionCard>
      </div>
    </AppShell>
  );
}
