import { Link } from '@inertiajs/react';
import { ArrowRight, ClipboardCheck, History, MessageCircle, Smile, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import HelpPill from '@/Components/shared/HelpPill';
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

type ActionTileProps = {
  icon: LucideIcon;
  title: string;
  description: string;
  href: string;
  /** Kelas warna kartu, lingkaran ikon, dan tombol panah. */
  tone: { card: string; icon: string; button: string };
};

const TONES = {
  yellow: { card: 'bg-mood-yellow text-text', icon: 'bg-card text-primary', button: 'bg-primary text-on-primary' },
  green: { card: 'bg-mood-green text-text', icon: 'bg-card text-primary', button: 'bg-primary text-on-primary' },
  brand: { card: 'bg-brand text-on-primary', icon: 'bg-on-primary/20 text-on-primary', button: 'bg-card text-primary' },
  blue: { card: 'bg-mood-blue text-text', icon: 'bg-card text-primary', button: 'bg-primary text-on-primary' },
} as const;

/** Kartu aksi menuju fitur. Seluruh kartu adalah tautan. */
function ActionTile({ icon: Icon, title, description, href, tone }: ActionTileProps) {
  return (
    <Link
      href={href}
      className={`group flex min-h-40 flex-col rounded-xl p-6 shadow-card transition-transform duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 motion-safe:hover:-translate-y-0.5 ${tone.card}`}
    >
      <span className={`grid size-11 place-items-center rounded-full ${tone.icon}`} aria-hidden="true">
        <Icon className="size-5.5" />
      </span>
      <h2 className="mt-4 text-lg font-semibold">{title}</h2>
      <p className="mt-1 text-sm">{description}</p>
      <span className="mt-auto flex justify-end pt-3" aria-hidden="true">
        <span
          className={`grid size-11 place-items-center rounded-full transition-transform motion-safe:group-hover:scale-105 ${tone.button}`}
        >
          <ArrowRight className="size-5" />
        </span>
      </span>
    </Link>
  );
}

const cardClass = 'rounded-xl border border-border bg-card shadow-card';

/** Gambar blob per tingkat mood (1 sampai 5). Di luar rentang jatuh ke "Biasa". */
function moodImage(mood: number): string {
  const level = mood >= 1 && mood <= 5 ? mood : 3;
  return `/images/mood/mood-${level}.webp`;
}

/** SCR-011 */
export default function Dashboard({ first_name, last_mood, last_attempt }: DashboardProps) {
  return (
    <AppShell title="Dashboard" showHelp={false}>
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h1>Halo, {first_name}.</h1>
          <p className="mt-2 text-text-secondary">Apa yang ingin kamu lakukan hari ini?</p>
        </div>
        <HelpPill className="hidden md:inline-flex" />
      </div>

      <div className="mt-8 grid items-start gap-6 lg:grid-cols-12">
        <div className="space-y-8 lg:col-span-7">
          <div className="grid gap-4 sm:grid-cols-2">
            <ActionTile
              icon={ClipboardCheck}
              title="Asesmen"
              description="Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu."
              href="/app/asesmen"
              tone={TONES.yellow}
            />
            <ActionTile
              icon={Smile}
              title="Mood Tracker"
              description="Catat perasaanmu dan lihat polanya dalam seminggu."
              href="/app/mood"
              tone={TONES.green}
            />
            <ActionTile
              icon={MessageCircle}
              title="RelaxMate"
              description="Teman bicara berbasis AI. Bukan psikolog atau layanan medis."
              href="/app/relaxmate"
              tone={TONES.brand}
            />
            <ActionTile
              icon={History}
              title="Riwayat"
              description="Lihat kembali hasil asesmen yang pernah kamu kerjakan."
              href="/app/riwayat"
              tone={TONES.blue}
            />
          </div>

          <section aria-labelledby="judul-asesmen">
            <div className="flex items-center justify-between gap-3">
              <h2 id="judul-asesmen" className="text-xl">
                Hasil asesmen terakhir
              </h2>
              {last_attempt ? (
                <Link
                  href="/app/riwayat"
                  className="inline-flex min-h-11 items-center text-sm font-medium text-brand-strong underline underline-offset-4"
                >
                  Lihat riwayat
                </Link>
              ) : null}
            </div>

            {last_attempt ? (
              <Link
                href={`/app/riwayat/${last_attempt.id}`}
                className={`${cardClass} mt-3 flex items-center gap-4 p-4 transition-shadow hover:shadow-popover`}
              >
                <span className="grid size-28 shrink-0 place-items-center rounded-xl bg-neutral-soft" aria-hidden="true">
                  <img
                    src="/images/landing/asesmen-checklist.webp"
                    width={96}
                    height={96}
                    alt=""
                    className="size-20 object-contain"
                  />
                </span>
                <span className="min-w-0 flex-1">
                  <span className="block text-lg font-semibold">{last_attempt.assessment_name}</span>
                  <span className="block text-sm text-text-secondary">{last_attempt.completed_at}</span>
                  <span className="mt-3 flex flex-wrap gap-2">
                    {last_attempt.sub_scales.map((item) => (
                      <span key={item.name} className="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text">
                        {item.name}: {item.interpretation}
                      </span>
                    ))}
                  </span>
                </span>
                <span
                  className="grid size-11 shrink-0 place-items-center rounded-full bg-primary text-on-primary"
                  aria-hidden="true"
                >
                  <ArrowRight className="size-5" />
                </span>
              </Link>
            ) : (
              <div className={`${cardClass} mt-3 flex flex-col items-center gap-4 p-5 sm:flex-row`}>
                <img
                  src="/images/asesmen/asesmen-kosong.webp"
                  width={96}
                  height={96}
                  alt=""
                  className="size-24 shrink-0 object-contain"
                />
                <p className="flex-1 text-center text-text-secondary sm:text-left">
                  Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu.
                </p>
                <Button asChild>
                  <Link href="/app/asesmen">Mulai asesmen</Link>
                </Button>
              </div>
            )}
          </section>
        </div>

        <div className="space-y-4 lg:col-span-5">
          <section aria-labelledby="judul-mood">
            <h2 id="judul-mood" className="text-xl">
              Mood terakhir
            </h2>
            <div className={`${cardClass} mt-3 flex flex-col items-center p-6 text-center`}>
              {last_mood ? (
                <>
                  <img
                    src={moodImage(last_mood.mood)}
                    width={160}
                    height={160}
                    alt=""
                    className="size-40 object-contain"
                  />
                  <p className="mt-2 text-lg font-medium">
                    {last_mood.mood_label}
                    {last_mood.arousal_label ? (
                      <span className="font-normal text-text-secondary"> · {last_mood.arousal_label}</span>
                    ) : null}
                  </p>
                  <p className="mt-1 text-sm text-text-secondary">{last_mood.when}</p>
                  <Button asChild variant="outline" className="mt-5 w-full">
                    <Link href="/app/mood">Catat mood</Link>
                  </Button>
                </>
              ) : (
                <>
                  <img
                    src="/images/mood/mood-row.webp"
                    width={240}
                    height={40}
                    alt=""
                    className="h-auto w-full max-w-60"
                  />
                  <p className="mt-4 max-w-xs text-text-secondary">
                    Belum ada catatan mood. Bagaimana perasaanmu hari ini?
                  </p>
                  <Button asChild className="mt-5 w-full">
                    <Link href="/app/mood">Catat mood</Link>
                  </Button>
                </>
              )}
            </div>
          </section>

          <section aria-label="Bantuan" className="rounded-xl bg-crisis-bg p-5 text-crisis-text">
            <h2 className="text-base">Butuh bantuan sekarang?</h2>
            <p className="mt-1 text-sm">Kamu tidak harus menghadapi ini sendirian.</p>
            <a
              href="/konsultasi"
              className="mt-1 inline-flex min-h-11 items-center text-sm font-medium underline underline-offset-2"
            >
              Lihat halaman Konsultasi Profesional
            </a>
          </section>
        </div>
      </div>
    </AppShell>
  );
}
