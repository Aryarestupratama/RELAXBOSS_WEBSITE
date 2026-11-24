import { Sparkles } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import {
  AI_REC_ENABLE,
  AI_REC_LABEL,
  AI_REC_LOADING,
  AI_REC_LOADING_HINT,
  AI_REC_NEEDS_CONSENT,
  AI_REC_RETRY,
  AI_REC_SKIP_TYPING,
  AI_REC_SUMMARY_TITLE,
  AI_REC_TITLE,
  AI_REC_WAITING_CONTEXT,
} from '@/lib/assessment';
import { useTypewriter } from '@/lib/typewriter';

type AiRecommendationCardProps =
  | { phase: 'consent'; onEnable: () => void }
  | { phase: 'context' }
  | { phase: 'loading' }
  | { phase: 'error'; message: string; retryable: boolean; onRetry: () => void }
  | {
      phase: 'ready';
      recommendation: string;
      summary: string | null;
      /** `true` bila hasil baru tiba di halaman ini (diketik); `false` untuk hasil tersimpan (langsung tampil). */
      animate: boolean;
    };

const linkClass = 'font-medium text-primary underline underline-offset-4';

/** Garis kerangka selagi AI menyusun: lebar berbeda-beda supaya terlihat seperti paragraf. */
const SKELETON_WIDTHS = ['w-full', 'w-[92%]', 'w-[96%]', 'w-[58%]'];

/**
 * CMP-004a (TASK-056). Kotak Rekomendasi AI di hasil Asesmen (SCR-014, FR-010): selalu hadir di tempat yang sama selama AI
 * relevan (butuh consent, menunggu konteks, menyusun, siap, atau gagal), jadi tidak lagi hilang lalu muncul lagi. Selagi AI
 * menyusun tampil animasi menunggu; begitu hasil tiba, ringkasan lalu rekomendasi diketik huruf demi huruf (hasil tersimpan
 * dari riwayat langsung tampil). Selalu berlabel "Disusun oleh AI" (`Badge`). Teks dirender sebagai teks biasa (bukan HTML),
 * jeda baris dipertahankan.
 */
export default function AiRecommendationCard(props: AiRecommendationCardProps) {
  return (
    <Card tone="lavender" role="region" aria-labelledby="ai-rec-title" className="gap-3 p-5">
      <CardHeader className="items-start gap-2">
        <Badge variant="outline">
          <Sparkles className="text-primary" aria-hidden="true" />
          {AI_REC_LABEL}
        </Badge>
        <CardTitle id="ai-rec-title" role="heading" aria-level={2} className="text-base font-bold">
          {AI_REC_TITLE}
        </CardTitle>
      </CardHeader>

      {props.phase === 'consent' ? (
        <CardContent>
          <p className="text-sm leading-relaxed text-text-secondary">
            {AI_REC_NEEDS_CONSENT}{' '}
            <button type="button" onClick={props.onEnable} className={`${linkClass} min-h-11`}>
              {AI_REC_ENABLE}
            </button>
          </p>
        </CardContent>
      ) : null}

      {props.phase === 'context' ? (
        <CardContent>
          <p className="text-sm leading-relaxed text-text-secondary">{AI_REC_WAITING_CONTEXT}</p>
        </CardContent>
      ) : null}

      {props.phase === 'loading' ? <Thinking /> : null}

      {props.phase === 'error' ? (
        <CardContent>
          <Alert>
            <AlertDescription>
              <p>{props.message}</p>
              {props.retryable ? (
                <Button type="button" variant="outline" className="mt-2 rounded-full! px-5 text-sm" onClick={props.onRetry}>
                  {AI_REC_RETRY}
                </Button>
              ) : null}
            </AlertDescription>
          </Alert>
        </CardContent>
      ) : null}

      {props.phase === 'ready' ? (
        <Result recommendation={props.recommendation} summary={props.summary} animate={props.animate} />
      ) : null}
    </Card>
  );
}

/** Animasi menunggu: ikon berdenyut, tiga titik, dan garis kerangka berkilau. Pembaca layar mendengar satu kalimat status. */
function Thinking() {
  return (
    <CardContent className="space-y-3" aria-busy="true">
      <p role="status" className="flex items-center gap-2.5 text-sm text-text-secondary">
        <Sparkles className="ai-sparkle size-4 text-primary" aria-hidden="true" />
        <span>{AI_REC_LOADING}</span>
        <span className="ai-dots" aria-hidden="true">
          <span />
          <span />
          <span />
        </span>
      </p>
      <div className="space-y-2.5 pt-1" aria-hidden="true">
        {SKELETON_WIDTHS.map((width) => (
          <div key={width} className={`ai-shimmer ${width}`} />
        ))}
      </div>
      <p className="text-[0.8125rem] text-text-secondary">{AI_REC_LOADING_HINT}</p>
    </CardContent>
  );
}

type ResultProps = { recommendation: string; summary: string | null; animate: boolean };

/** Hasil AI: ringkasan lalu rekomendasi, diketik bila baru tiba. Tinggi kotak sudah final sejak awal, jadi tidak melompat. */
function Result({ recommendation, summary, animate }: ResultProps) {
  const texts = summary ? [summary, recommendation] : [recommendation];
  const { shown, done, skip } = useTypewriter(texts, animate);
  const typing = animate && !done;
  const caretIn = shown.findIndex((count, index) => count < Array.from(texts[index]).length);

  return (
    <>
      {summary ? (
        <>
          <CardContent>
            <h3 className="text-[0.8125rem] font-semibold text-text-secondary">{AI_REC_SUMMARY_TITLE}</h3>
            <Typed text={summary} shown={shown[0]} caret={typing && caretIn === 0} animate={animate} className="mt-0.5" />
          </CardContent>
          <Separator className="bg-text/10" />
        </>
      ) : null}

      <CardContent>
        <Typed
          text={recommendation}
          shown={shown[summary ? 1 : 0]}
          caret={typing && caretIn === (summary ? 1 : 0)}
          animate={animate}
        />
      </CardContent>

      {typing ? (
        <div className="flex justify-end">
          <Button type="button" variant="ghost" className="min-h-11 rounded-full! px-4 text-[0.8125rem]" onClick={skip}>
            {AI_REC_SKIP_TYPING}
          </Button>
        </div>
      ) : null}
    </>
  );
}

type TypedProps = { text: string; shown: number; caret: boolean; animate: boolean; className?: string };

/**
 * Satu paragraf. Saat diketik, salinan tak terlihat dari teks penuh menahan tinggi paragraf (satu sel grid yang sama dengan
 * teks yang tampil), dan pembaca layar mendapat teks penuh sekali lewat `sr-only` alih-alih huruf demi huruf.
 */
function Typed({ text, shown, caret, animate, className }: TypedProps) {
  const base = `text-sm leading-relaxed whitespace-pre-line ${className ?? ''}`;
  if (!animate) {
    return <p className={base}>{text}</p>;
  }
  const visible = Array.from(text).slice(0, shown).join('');
  return (
    <div className={base}>
      <p className="sr-only">{text}</p>
      <div className="grid" aria-hidden="true">
        <span className="invisible col-start-1 row-start-1">{text}</span>
        <span className="col-start-1 row-start-1">
          {visible}
          {caret ? <span className="ai-caret" /> : null}
        </span>
      </div>
    </div>
  );
}
