import { Sparkles } from 'lucide-react';
import { AI_REC_LABEL, AI_REC_SUMMARY_TITLE, AI_REC_TITLE } from '@/lib/assessment';

type AiRecommendationCardProps = {
  recommendation: string;
  summary: string | null;
};

/**
 * Rekomendasi AI di hasil Asesmen (SCR-014, FR-010). Selalu berlabel "Disusun oleh AI".
 * Teks dirender sebagai teks biasa (bukan HTML), jeda baris dipertahankan.
 */
export default function AiRecommendationCard({ recommendation, summary }: AiRecommendationCardProps) {
  return (
    <section aria-labelledby="ai-rec-title" className="rounded-3xl bg-soft-lavender p-5 text-text shadow-lift">
      <p className="flex w-fit items-center gap-1.5 rounded-full bg-card/60 px-3 py-1 text-xs font-medium text-text">
        <Sparkles className="size-4 text-brand-strong" aria-hidden="true" />
        {AI_REC_LABEL}
      </p>
      <h2 id="ai-rec-title" className="mt-2 text-base font-bold">
        {AI_REC_TITLE}
      </h2>

      {summary ? (
        <div className="mt-3">
          <h3 className="text-[0.8125rem] font-semibold text-text-secondary">{AI_REC_SUMMARY_TITLE}</h3>
          <p className="mt-0.5 text-sm leading-relaxed whitespace-pre-line">{summary}</p>
        </div>
      ) : null}

      <p className="mt-3 text-sm leading-relaxed whitespace-pre-line">{recommendation}</p>
    </section>
  );
}
