import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Clock, ClipboardCheck, Info, ListChecks } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';

type ShowProps = {
  assessment: {
    slug: string;
    name: string;
    description: string;
    instructions: string | null;
    estimated_minutes: number;
    question_count: number;
    sub_scales: string[];
    options: string[];
    source: string | null;
    validated_by: string | null;
    is_demo: boolean;
  };
};

/** Pastel yang sama dengan halaman info Asesmen Publik (teks di atasnya `--text`, RULE-052). */
const TONES = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'] as const;

const cardClass = 'rounded-3xl bg-card shadow-lift';

/** Detail Asesmen sebelum mengerjakan (setara SCR-002 untuk pengguna yang sudah masuk). */
export default function Show({ assessment }: ShowProps) {
  const hasSource = assessment.source !== null || assessment.validated_by !== null;

  return (
    <AppShell title={assessment.name} wide staggered>
      <div data-load>
        <Link
          href="/app/asesmen"
          className="group inline-flex min-h-11 items-center gap-2 text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-text"
        >
          <ArrowLeft
            className="size-4 transition-transform duration-200 group-hover:-translate-x-0.5"
            aria-hidden="true"
          />
          Semua asesmen
        </Link>
      </div>

      <div className="mt-1 grid items-start gap-4 lg:grid-cols-12">
        <div className="space-y-4 lg:col-span-8">
          <section data-load className={`${cardClass} p-5 md:p-6`}>
            <h1 className="text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">{assessment.name}</h1>
            {assessment.is_demo ? (
              <p className="mt-2 w-fit rounded-full bg-soft-peach px-3 py-1 text-xs font-medium text-text">
                Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.
              </p>
            ) : null}
            <p className="mt-3 max-w-prose text-sm leading-relaxed text-text-secondary">{assessment.description}</p>
            <div className="mt-5">
              <Link
                href={`/app/asesmen/${assessment.slug}/kerjakan`}
                data-press
                className="group inline-flex min-h-11 items-center justify-center gap-2 rounded-full border-[1.5px] border-primary bg-primary px-6 text-sm font-medium text-on-primary transition duration-200 hover:border-nav-hover hover:bg-nav-hover hover:shadow-card"
              >
                Mulai asesmen
                <ArrowRight
                  className="size-4 transition-transform duration-200 group-hover:translate-x-0.5"
                  aria-hidden="true"
                />
              </Link>
            </div>
          </section>

          {assessment.sub_scales.length > 0 ? (
            <section data-load aria-labelledby="judul-aspek">
              <h2 id="judul-aspek" className="text-base font-semibold">
                Yang dilihat
              </h2>
              <p className="mt-0.5 text-[0.8125rem] text-text-secondary">
                Hasilmu ditampilkan terpisah untuk tiap aspek berikut.
              </p>
              <ul className="mt-2 flex flex-wrap gap-2">
                {assessment.sub_scales.map((name, position) => (
                  <li key={name}>
                    <span
                      className={`inline-block rounded-2xl px-4 py-2 text-sm font-semibold text-text shadow-lift ${TONES[position % TONES.length]}`}
                    >
                      {name}
                    </span>
                  </li>
                ))}
              </ul>
            </section>
          ) : null}

          <section data-load aria-labelledby="judul-cara" className={`${cardClass} p-5 md:p-6`}>
            <h2 id="judul-cara" className="text-base font-semibold">
              Cara mengerjakan
            </h2>
            {assessment.instructions ? (
              <p className="mt-2 max-w-prose text-sm leading-relaxed text-text-secondary">{assessment.instructions}</p>
            ) : null}
            <p className="mt-2 max-w-prose text-sm leading-relaxed text-text-secondary">
              Satu pertanyaan tampil pada satu waktu. Tidak ada jawaban benar atau salah, jawab sesuai keadaanmu.
            </p>
            {assessment.options.length > 0 ? (
              <>
                <h3 className="mt-4 text-[0.8125rem] font-semibold">Pilihan jawaban</h3>
                <ul className="mt-2 flex flex-wrap gap-1.5">
                  {assessment.options.map((option) => (
                    <li key={option} className="rounded-full bg-soft-sky px-3 py-1 text-xs font-medium text-text">
                      {option}
                    </li>
                  ))}
                </ul>
              </>
            ) : null}
          </section>
        </div>

        <div className="space-y-4 lg:col-span-4">
          <section data-load="pop" aria-labelledby="judul-sekilas" className="rounded-3xl bg-soft-mint p-5 text-text shadow-lift">
            <span className="grid size-10 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
              <ClipboardCheck className="size-5" />
            </span>
            <h2 id="judul-sekilas" className="mt-3 text-base font-bold">
              Sekilas
            </h2>
            <dl className="mt-3 space-y-3 text-sm">
              <div className="flex items-center gap-3">
                <span className="grid size-9 shrink-0 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
                  <Clock className="size-4" />
                </span>
                <div>
                  <dt className="text-xs text-text-secondary">Waktu pengerjaan</dt>
                  <dd className="font-semibold">Sekitar {assessment.estimated_minutes} menit</dd>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <span className="grid size-9 shrink-0 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
                  <ListChecks className="size-4" />
                </span>
                <div>
                  <dt className="text-xs text-text-secondary">Jumlah pertanyaan</dt>
                  <dd className="font-semibold">{assessment.question_count} pertanyaan</dd>
                </div>
              </div>
            </dl>
          </section>

          {hasSource ? (
            <section data-load aria-labelledby="judul-sumber" className={`${cardClass} p-5`}>
              <h2 id="judul-sumber" className="text-base font-semibold">
                Tentang instrumen
              </h2>
              <dl className="mt-2 space-y-2 text-sm">
                {assessment.source ? (
                  <div>
                    <dt className="text-xs text-text-secondary">Sumber</dt>
                    <dd>{assessment.source}</dd>
                  </div>
                ) : null}
                {assessment.validated_by ? (
                  <div>
                    <dt className="text-xs text-text-secondary">Divalidasi oleh</dt>
                    <dd>{assessment.validated_by}</dd>
                  </div>
                ) : null}
              </dl>
            </section>
          ) : null}

          <p
            data-load
            role="note"
            className="flex items-start gap-2 px-1 text-[0.8125rem] leading-relaxed text-text-secondary"
          >
            <Info className="mt-0.5 size-4 shrink-0 text-primary" aria-hidden="true" />
            <span>
              Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang
              tepat, bicarakan dengan psikolog atau tenaga profesional.{' '}
              <a href="/konsultasi" className="font-semibold text-primary underline underline-offset-4">
                Bicara dengan profesional
              </a>
            </span>
          </p>
        </div>
      </div>
    </AppShell>
  );
}
