import { useCallback, useEffect, useRef, useState } from 'react';
import { AI_REC_FALLBACK_ERROR } from '@/lib/assessment';
import { postJson } from '@/lib/http';

export type SavedRecommendation = { recommendation: string; summary: string | null };

export type AiRecommendationState =
  | { status: 'idle' }
  | { status: 'loading' }
  | { status: 'ready'; recommendation: string; summary: string | null }
  | { status: 'error'; message: string; retryable: boolean };

/** Galat yang tidak berguna bila diulang langsung. */
const FINAL_ERRORS = new Set(['daily_limit', 'ai_skipped', 'consent_required', 'context_pending']);

/**
 * Meminta Rekomendasi AI (API-010) sekali saat `enabled` menjadi true, kecuali hasil sudah tersimpan.
 * Gagal apa pun jatuh ke rekomendasi statis (FR-010); `retry` mengulang atas permintaan pengguna.
 */
export function useAiRecommendation(attemptId: number, saved: SavedRecommendation | null, enabled: boolean) {
  const [state, setState] = useState<AiRecommendationState>(
    saved ? { status: 'ready', recommendation: saved.recommendation, summary: saved.summary } : { status: 'idle' },
  );
  const requested = useRef(false);
  const mounted = useRef(false);

  useEffect(() => {
    mounted.current = true;

    return () => {
      mounted.current = false;
    };
  }, []);

  const request = useCallback(async () => {
    setState({ status: 'loading' });

    const result = await postJson<SavedRecommendation>(`/app/riwayat/${attemptId}/rekomendasi`, {});

    if (!mounted.current) {
      return;
    }

    if (result.ok) {
      setState({
        status: 'ready',
        recommendation: result.data.recommendation,
        summary: result.data.summary ?? null,
      });

      return;
    }

    setState({
      status: 'error',
      message: result.message || AI_REC_FALLBACK_ERROR,
      retryable: !FINAL_ERRORS.has(result.code),
    });
  }, [attemptId]);

  useEffect(() => {
    if (saved || !enabled || requested.current) {
      return;
    }

    requested.current = true;
    void request();
  }, [saved, enabled, request]);

  return { state, retry: request };
}
