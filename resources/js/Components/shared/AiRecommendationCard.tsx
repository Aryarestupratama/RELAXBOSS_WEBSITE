import { Sparkles } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { AI_REC_LABEL, AI_REC_SUMMARY_TITLE, AI_REC_TITLE } from '@/lib/assessment';

type AiRecommendationCardProps = {
  recommendation: string;
  summary: string | null;
};

/**
 * Rekomendasi AI di hasil Asesmen (SCR-014, FR-010). Selalu berlabel "Disusun oleh AI" (`Badge`).
 * Teks dirender sebagai teks biasa (bukan HTML), jeda baris dipertahankan.
 */
export default function AiRecommendationCard({ recommendation, summary }: AiRecommendationCardProps) {
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

      {summary ? (
        <>
          <CardContent>
            <h3 className="text-[0.8125rem] font-semibold text-text-secondary">{AI_REC_SUMMARY_TITLE}</h3>
            <p className="mt-0.5 text-sm leading-relaxed whitespace-pre-line">{summary}</p>
          </CardContent>
          <Separator className="bg-text/10" />
        </>
      ) : null}

      <CardContent>
        <p className="text-sm leading-relaxed whitespace-pre-line">{recommendation}</p>
      </CardContent>
    </Card>
  );
}
