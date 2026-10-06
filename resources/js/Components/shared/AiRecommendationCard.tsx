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
    <section aria-labelledby="ai-rec-title" className="rounded-xl border border-border bg-card p-5 shadow-card">
      <p className="flex items-center gap-2 text-sm text-text-secondary">
        <Sparkles className="size-4 text-brand-strong" aria-hidden="true" />
        {AI_REC_LABEL}
      </p>
      <h2 id="ai-rec-title" className="mt-1 text-lg font-semibold">
        {AI_REC_TITLE}
      </h2>

      {summary ? (
        <div className="mt-3">
          <h3 className="text-sm font-medium text-text-secondary">{AI_REC_SUMMARY_TITLE}</h3>
          <p className="mt-0.5 whitespace-pre-line">{summary}</p>
        </div>
      ) : null}

      <p className="mt-3 whitespace-pre-line">{recommendation}</p>
    </section>
  );
}
