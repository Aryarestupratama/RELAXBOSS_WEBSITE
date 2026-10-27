import { Link } from '@inertiajs/react';
import { ArrowRight, ArrowUpRight, ClipboardCheck, History, LifeBuoy, MessageCircle, Smile, type LucideIcon } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { NotchCorner } from '@/Components/ui/notch-corner';

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

type CardTone = 'lavender' | 'sky' | 'mint' | 'peach';

type ActionTileProps = {
  icon: LucideIcon;
  title: string;
  description: string;
  href: string;
  /** Warna pastel kartu (teks di atasnya selalu --text, RULE-052). */
  tone: CardTone;
};

/**
 * Kartu aksi menuju fitur (shadcn Card), satu bahasa dengan kartu fitur Beranda: pastel, sudut
 * membulat besar, dan sudut kanan bawah bertakik dengan cakram panah (NotchCorner) yang berbalik warna saat disorot.
 * Seluruh kartu adalah satu tautan.
 */
function ActionTile({ icon: Icon, title, description, href, tone }: ActionTileProps) {
  return (
    <Link
      href={href}
      data-load
      data-lift
      data-press
      className="group relative block rounded-3xl transition-shadow duration-300 hover:shadow-lift-hover"
    >
      <Card tone={tone} className="min-h-36 gap-1 p-4 pr-16 transition-shadow duration-300 group-hover:shadow-lift-hover">
        <CardHeader className="gap-0">
          <span className="grid size-10 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
            <Icon className="size-5" />
          </span>
          <CardTitle className="mt-3 text-base font-bold">{title}</CardTitle>
        </CardHeader>
        <CardContent>
          <CardDescription className="text-[0.8125rem] leading-relaxed">{description}</CardDescription>
        </CardContent>
      </Card>
      <NotchCorner>
        <ArrowUpRight className="size-5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
      </NotchCorner>
    </Link>
  );
}

/** Gambar blob per tingkat mood (1 sampai 5). Di luar rentang jatuh ke "Biasa". */
function moodImage(mood: number): string {
  const level = mood >= 1 && mood <= 5 ? mood : 3;
  return `/images/mood/mood-${level}.webp`;
}

/** SCR-011 */
export default function Dashboard({ first_name, last_mood, last_attempt }: DashboardProps) {
  return (
    <AppShell title="Dashboard" wide staggered>
      <PageHeader title={`Halo, ${first_name}.`} description="Apa yang ingin kamu lakukan hari ini?" />

      <div className="grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-5 lg:col-span-8">
          <div className="grid gap-3 sm:grid-cols-2">
            <ActionTile
              icon={ClipboardCheck}
              title="Asesmen"
              description="Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu."
              href="/app/asesmen"
              tone="peach"
            />
            <ActionTile
              icon={Smile}
              title="Mood Tracker"
              description="Catat perasaanmu dan lihat polanya dalam seminggu."
              href="/app/mood"
              tone="mint"
            />
            <ActionTile
              icon={MessageCircle}
              title="RelaxMate"
              description="Teman bicara berbasis AI. Bukan psikolog atau layanan medis."
              href="/app/relaxmate"
              tone="lavender"
            />
            <ActionTile
              icon={History}
              title="Riwayat"
              description="Lihat kembali hasil asesmen yang pernah kamu kerjakan."
              href="/app/riwayat"
              tone="sky"
            />
          </div>

          <section aria-labelledby="judul-asesmen" data-load>
            <Card className="gap-3 rounded-2xl p-4">
              <CardHeader className="flex-row items-center justify-between gap-3">
                <CardTitle id="judul-asesmen" className="text-base">
                  <h2>Hasil asesmen terakhir</h2>
                </CardTitle>
                {last_attempt ? (
                  <Button asChild variant="outline" data-press className="rounded-full! px-4 text-[0.8125rem]">
                    <Link href="/app/riwayat">
                      <History className="size-4" aria-hidden="true" />
                      Lihat riwayat
                    </Link>
                  </Button>
                ) : null}
              </CardHeader>

              {last_attempt ? (
                <CardContent>
                  <Link
                    href={`/app/riwayat/${last_attempt.id}`}
                    data-lift
                    data-press
                    className="group flex items-center gap-3 rounded-xl border border-border/60 bg-card px-4 py-3 transition-shadow duration-300 hover:shadow-lift-hover"
                  >
                    <span className="min-w-0 flex-1">
                      <span className="block text-[0.9375rem] leading-snug font-semibold">{last_attempt.assessment_name}</span>
                      <span className="block text-[0.8125rem] text-text-secondary">{last_attempt.completed_at}</span>
                      <span className="mt-1.5 flex flex-wrap gap-1.5">
                        {last_attempt.sub_scales.map((item) => (
                          <Badge key={item.name} variant="outline">
                            {item.name}: {item.interpretation}
                          </Badge>
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
                </CardContent>
              ) : (
                <CardContent className="flex flex-col items-center gap-3 rounded-xl border border-border/60 bg-card p-4 sm:flex-row">
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
                </CardContent>
              )}
            </Card>
          </section>
        </div>

        <div className="space-y-4 lg:col-span-4">
          <section aria-labelledby="judul-mood" data-load>
            <Card className="gap-0 overflow-hidden rounded-2xl">
              <CardHeader className="px-4 pt-4 pb-3">
                <CardTitle id="judul-mood" className="text-base">
                  <h2>Mood terakhir</h2>
                </CardTitle>
              </CardHeader>
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
              <CardContent className="p-4 text-center">
                {last_mood ? (
                  <>
                    <p className="text-base font-semibold">
                      {last_mood.mood_label}
                      {last_mood.arousal_label ? (
                        <span className="font-normal text-text-secondary"> · {last_mood.arousal_label}</span>
                      ) : null}
                    </p>
                    <p className="mt-0.5 text-[0.8125rem] text-text-secondary">{last_mood.when}</p>
                  </>
                ) : (
                  <p className="mx-auto max-w-xs text-sm text-text-secondary">
                    Belum ada catatan mood. Bagaimana perasaanmu hari ini?
                  </p>
                )}
              </CardContent>
              <CardFooter className="px-4 pb-4">
                <Button asChild variant={last_mood ? 'outline' : 'default'} className="w-full rounded-full! text-sm">
                  <Link href="/app/mood">Catat mood</Link>
                </Button>
              </CardFooter>
            </Card>
          </section>

          <section aria-label="Bantuan" data-load>
            <Card className="gap-1 rounded-2xl border-crisis-text/30 bg-crisis-bg p-4 text-crisis-text">
              <CardHeader className="flex-row items-center gap-2">
                <LifeBuoy className="size-4.5 shrink-0" aria-hidden="true" />
                <CardTitle className="text-[0.9375rem]">
                  <h2>Butuh bantuan sekarang?</h2>
                </CardTitle>
              </CardHeader>
              <CardContent className="text-[0.8125rem]">
                <p>Kamu tidak harus menghadapi ini sendirian.</p>
                <a
                  href="/konsultasi"
                  className="mt-0.5 inline-flex min-h-11 items-center font-medium underline underline-offset-4"
                >
                  Lihat halaman Konsultasi Profesional
                </a>
              </CardContent>
            </Card>
          </section>
        </div>
      </div>
    </AppShell>
  );
}
