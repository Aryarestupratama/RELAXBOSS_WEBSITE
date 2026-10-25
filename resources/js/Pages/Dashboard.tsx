import { Link } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, ClipboardCheck, History, LifeBuoy, MessageCircle, Smile, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
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
  /** Kelas warna pastel kartu (teks di atasnya selalu --text, RULE-052). */
  tone: string;
};

/* Pastel yang sama dengan kartu Beranda publik (TASK-028Q). */
const TONES = {
  lavender: 'bg-soft-lavender',
  sky: 'bg-soft-sky',
  mint: 'bg-soft-mint',
  peach: 'bg-soft-peach',
} as const;

/**
 * Kartu aksi menuju fitur, satu bahasa dengan kartu fitur Beranda: pastel, sudut membulat besar, dan
 * "tab" panah di pojok kanan bawah yang berbalik warna saat disorot. Seluruh kartu adalah tautan.
 */
function ActionTile({ icon: Icon, title, description, href, tone }: ActionTileProps) {
  return (
    <Link
      href={href}
      data-load
      data-lift
      data-press
      className={`group relative flex min-h-36 flex-col rounded-3xl p-4 pr-16 pb-4 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover ${tone}`}
    >
      <span className="grid size-10 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
        <Icon className="size-5" />
      </span>
      <span className="mt-3 block text-base leading-snug font-bold">{title}</span>
      <span className="mt-1 block text-[0.8125rem] leading-relaxed text-text-secondary">{description}</span>
      <span
        className="absolute right-0 bottom-0 grid h-11 w-12 place-items-center rounded-tl-[18px] rounded-br-3xl bg-neutral-soft text-primary transition-colors duration-200 group-hover:bg-primary group-hover:text-on-primary"
        aria-hidden="true"
      >
        <ArrowUpRight className="size-4.5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
      </span>
    </Link>
  );
}

const cardClass = 'rounded-2xl border border-border bg-card shadow-lift';

/** Gambar blob per tingkat mood (1 sampai 5). Di luar rentang jatuh ke "Biasa". */
function moodImage(mood: number): string {
  const level = mood >= 1 && mood <= 5 ? mood : 3;
  return `/images/mood/mood-${level}.webp`;
}

/** SCR-011 */
export default function Dashboard({ first_name, last_mood, last_attempt }: DashboardProps) {
  return (
    <AppShell title="Dashboard" wide staggered>
      <div data-load>
        <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">Halo, {first_name}.</h1>
        <p className="mt-1 text-sm text-text-secondary">Apa yang ingin kamu lakukan hari ini?</p>
      </div>

      <div className="mt-5 grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-5 lg:col-span-8">
          <div className="grid gap-3 sm:grid-cols-2">
            <ActionTile
              icon={ClipboardCheck}
              title="Asesmen"
              description="Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu."
              href="/app/asesmen"
              tone={TONES.peach}
            />
            <ActionTile
              icon={Smile}
              title="Mood Tracker"
              description="Catat perasaanmu dan lihat polanya dalam seminggu."
              href="/app/mood"
              tone={TONES.mint}
            />
            <ActionTile
              icon={MessageCircle}
              title="RelaxMate"
              description="Teman bicara berbasis AI. Bukan psikolog atau layanan medis."
              href="/app/relaxmate"
              tone={TONES.lavender}
            />
            <ActionTile
              icon={History}
              title="Riwayat"
              description="Lihat kembali hasil asesmen yang pernah kamu kerjakan."
              href="/app/riwayat"
              tone={TONES.sky}
            />
          </div>

          <section aria-labelledby="judul-asesmen" data-load>
            <div className="flex items-center justify-between gap-3">
              <h2 id="judul-asesmen" className="text-base font-semibold">
                Hasil asesmen terakhir
              </h2>
              {last_attempt ? (
                <Link
                  href="/app/riwayat"
                  className="inline-flex min-h-11 items-center text-[0.8125rem] font-medium text-brand-strong underline underline-offset-4"
                >
                  Lihat riwayat
                </Link>
              ) : null}
            </div>

            {last_attempt ? (
              <Link
                href={`/app/riwayat/${last_attempt.id}`}
                data-lift
                data-press
                className={`${cardClass} group mt-1 flex items-center gap-3 p-3 transition-shadow duration-300 hover:shadow-lift-hover`}
              >
                <span className="grid size-14 shrink-0 place-items-center rounded-xl bg-soft-peach" aria-hidden="true">
                  <img
                    src="/images/landing/asesmen-checklist.webp"
                    width={56}
                    height={56}
                    alt=""
                    className="size-10 object-contain"
                  />
                </span>
                <span className="min-w-0 flex-1">
                  <span className="block text-[0.9375rem] leading-snug font-semibold">{last_attempt.assessment_name}</span>
                  <span className="block text-[0.8125rem] text-text-secondary">{last_attempt.completed_at}</span>
                  <span className="mt-1.5 flex flex-wrap gap-1.5">
                    {last_attempt.sub_scales.map((item) => (
                      <span key={item.name} className="rounded-full bg-neutral-soft px-2.5 py-0.5 text-xs text-text">
                        {item.name}: {item.interpretation}
                      </span>
                    ))}
                  </span>
                </span>
                <span
                  className="grid size-9 shrink-0 place-items-center rounded-full border-[1.5px] border-primary text-primary transition-colors duration-200 group-hover:bg-primary group-hover:text-on-primary"
                  aria-hidden="true"
                >
                  <ArrowRight className="size-4 transition-transform duration-200 group-hover:translate-x-0.5" />
                </span>
              </Link>
            ) : (
              <div className={`${cardClass} mt-1 flex flex-col items-center gap-3 p-4 sm:flex-row`}>
                <img
                  src="/images/asesmen/asesmen-kosong.webp"
                  width={96}
                  height={96}
                  alt=""
                  className="size-16 shrink-0 object-contain"
                />
                <p className="flex-1 text-center text-sm text-text-secondary sm:text-left">
                  Belum ada hasil. Mulai Asesmen pertamamu untuk mengenali kondisimu.
                </p>
                <Button asChild className="rounded-full! px-5 text-sm">
                  <Link href="/app/asesmen">Mulai asesmen</Link>
                </Button>
              </div>
            )}
          </section>
        </div>

        <div className="space-y-4 lg:col-span-4">
          <section aria-labelledby="judul-mood" data-load>
            <h2 id="judul-mood" className="text-base font-semibold">
              Mood terakhir
            </h2>
            <div className={`${cardClass} mt-1 overflow-hidden`}>
              <div className="grid place-items-center bg-soft-lavender px-4 py-4">
                {last_mood ? (
                  <img
                    src={moodImage(last_mood.mood)}
                    width={112}
                    height={112}
                    alt=""
                    data-float
                    className="size-24 object-contain"
                  />
                ) : (
                  <img src="/images/mood/mood-row.webp" width={240} height={40} alt="" className="h-auto w-full max-w-48" />
                )}
              </div>
              <div className="p-4 text-center">
                {last_mood ? (
                  <>
                    <p className="text-base font-semibold">
                      {last_mood.mood_label}
                      {last_mood.arousal_label ? (
                        <span className="font-normal text-text-secondary"> · {last_mood.arousal_label}</span>
                      ) : null}
                    </p>
                    <p className="mt-0.5 text-[0.8125rem] text-text-secondary">{last_mood.when}</p>
                    <Button asChild variant="outline" className="mt-3 w-full rounded-full! text-sm">
                      <Link href="/app/mood">Catat mood</Link>
                    </Button>
                  </>
                ) : (
                  <>
                    <p className="mx-auto max-w-xs text-sm text-text-secondary">
                      Belum ada catatan mood. Bagaimana perasaanmu hari ini?
                    </p>
                    <Button asChild className="mt-3 w-full rounded-full! text-sm">
                      <Link href="/app/mood">Catat mood</Link>
                    </Button>
                  </>
                )}
              </div>
            </div>
          </section>

          <section aria-label="Bantuan" data-load className="rounded-2xl bg-crisis-bg p-4 text-crisis-text">
            <div className="flex items-center gap-2">
              <LifeBuoy className="size-4.5 shrink-0" aria-hidden="true" />
              <h2 className="text-[0.9375rem] font-semibold">Butuh bantuan sekarang?</h2>
            </div>
            <p className="mt-1 text-[0.8125rem]">Kamu tidak harus menghadapi ini sendirian.</p>
            <a
              href="/konsultasi"
              className="mt-0.5 inline-flex min-h-11 items-center text-[0.8125rem] font-medium underline underline-offset-4"
            >
              Lihat halaman Konsultasi Profesional
            </a>
          </section>
        </div>
      </div>
    </AppShell>
  );
}
