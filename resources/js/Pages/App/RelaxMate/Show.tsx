import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ChatBubble from '@/Components/shared/ChatBubble';
import ChatInput from '@/Components/shared/ChatInput';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import CrisisBanner from '@/Components/shared/CrisisBanner';
import SuggestionCard from '@/Components/shared/SuggestionCard';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { postJson } from '@/lib/http';
import {
  RELAXMATE_CONSENT_NEEDED,
  RELAXMATE_DAILY_LIMIT,
  RELAXMATE_NOTE,
  RELAXMATE_TYPING,
  RELAXMATE_UNANSWERED,
  type ChatMessage,
  type SendResponse,
} from '@/lib/relaxmate';

type ShowProps = {
  conversation: { id: string; label: string; has_crisis: boolean };
  messages: ChatMessage[];
  unanswered: boolean;
  limits: { message_max_chars: number; per_day: number; remaining_today: number };
};

/** SCR-017 (Percakapan). Pesan dikirim lewat fetch JSON (API-015); riwayat dari server. */
export default function Show({ conversation, messages: initialMessages, unanswered: initialUnanswered, limits }: ShowProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;

  const [messages, setMessages] = useState<ChatMessage[]>(initialMessages);
  const [hasCrisis, setHasCrisis] = useState(conversation.has_crisis);
  const [remaining, setRemaining] = useState(limits.remaining_today);
  const [unanswered, setUnanswered] = useState(initialUnanswered);
  const [draft, setDraft] = useState('');
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [dismissed, setDismissed] = useState<Set<number | string>>(new Set());
  const [consentOpen, setConsentOpen] = useState(needsConsent(consent));

  const endRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);

  // Sinkron ulang bila server mengirim data baru (mis. setelah muat ulang).
  useEffect(() => {
    setMessages(initialMessages);
    setUnanswered(initialUnanswered);
    setHasCrisis(conversation.has_crisis);
    setRemaining(limits.remaining_today);
  }, [initialMessages, initialUnanswered, conversation.has_crisis, limits.remaining_today]);

  useEffect(() => {
    endRef.current?.scrollIntoView({ block: 'end' });
  }, [messages.length, sending]);

  const consentDone = consent?.complete ?? false;
  const limitReached = remaining <= 0;
  const canType = consentDone && !limitReached;
  const lastAssistantIndex = messages.map((m) => m.role).lastIndexOf('assistant');

  const send = async (retry: boolean) => {
    if (sending) {
      return;
    }

    const content = draft.trim();
    if (!retry) {
      if (content === '') {
        setError('Tulis pesanmu dulu.');
        return;
      }
      if (content.length > limits.message_max_chars) {
        setError(`Pesan terlalu panjang. Maksimal ${limits.message_max_chars} karakter.`);
        return;
      }
    }

    setSending(true);
    setError(null);

    const tmpId = `tmp-${Date.now()}`;
    if (!retry) {
      setMessages((prev) => [
        ...prev,
        { id: tmpId, role: 'user', content, time: '', is_crisis: false, suggestion: null, pending: true },
      ]);
      setDraft('');
    }

    const result = await postJson<SendResponse>(
      `/app/relaxmate/${conversation.id}/pesan`,
      retry ? { retry: true } : { content },
    );
    setSending(false);

    if (result.ok) {
      const { user_message, assistant_message, has_crisis, remaining_today } = result.data;
      setMessages((prev) => {
        // Buang pesan sementara (termasuk dari percobaan yang gagal), lalu pakai versi server.
        const kept = prev.filter((m) => typeof m.id === 'number');
        const hasUser = kept.some((m) => m.id === user_message.id);
        return [...(hasUser ? kept : [...kept, user_message]), assistant_message];
      });
      setUnanswered(false);
      setHasCrisis(has_crisis);
      setRemaining(remaining_today);
      inputRef.current?.focus();
      return;
    }

    if (result.code === 'ai_unavailable') {
      // Pesan sudah tersimpan di server: tetap tampil, tawarkan kirim ulang (FR-017).
      setMessages((prev) => prev.map((m) => (m.id === tmpId ? { ...m, pending: false } : m)));
      setUnanswered(true);
      setError(result.message);
      if (!retry) {
        setRemaining((r) => Math.max(0, r - 1));
      }
      return;
    }

    // Selain itu pesan tidak diproses: kembalikan teks ke kolom agar tidak hilang.
    if (!retry) {
      setMessages((prev) => prev.filter((m) => m.id !== tmpId));
      setDraft(content);
    }

    if (result.code === 'consent_required') {
      setConsentOpen(true);
      return;
    }
    if (result.code === 'daily_limit') {
      setRemaining(0);
    }
    if (result.code === 'nothing_to_retry') {
      router.reload();
    }
    setError(result.message);
    inputRef.current?.focus();
  };

  return (
    <AppShell title="RelaxMate">
      <Link
        href="/app/relaxmate"
        className="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong"
      >
        <ArrowLeft className="size-4" aria-hidden="true" />
        Semua percakapan
      </Link>

      <h1 className="mt-2">RelaxMate</h1>
      <p className="mt-1 text-sm text-text-secondary">
        {conversation.label}. {RELAXMATE_NOTE} Butuh bantuan sekarang?{' '}
        <a href="/konsultasi" className="font-medium text-brand-strong underline underline-offset-2">
          Konsultasi Profesional
        </a>
      </p>

      {hasCrisis ? (
        <div id="bantuan" tabIndex={-1} className="mt-4 outline-none">
          <CrisisBanner />
        </div>
      ) : null}

      <ol
        role="log"
        aria-live="polite"
        aria-label="Percakapan"
        className="mt-6 flex flex-col gap-4"
      >
        {messages.map((message, index) => {
          const suggestion = message.suggestion;
          const visible =
            suggestion !== null &&
            !dismissed.has(message.id) &&
            (suggestion.priority === 1 || index === lastAssistantIndex);

          return (
            <ChatBubble key={message.id} message={message}>
              {visible && suggestion ? (
                <SuggestionCard
                  suggestion={suggestion}
                  onDismiss={() => setDismissed((prev) => new Set(prev).add(message.id))}
                />
              ) : null}
            </ChatBubble>
          );
        })}

        {sending ? (
          <li className="flex items-start">
            <p role="status" className="rounded-2xl rounded-bl-md border border-border bg-card px-4 py-3 text-text-secondary">
              {RELAXMATE_TYPING}
            </p>
          </li>
        ) : null}
      </ol>
      <div ref={endRef} />

      <div className="mt-6 grid gap-3">
        {(error || unanswered) && !sending ? (
          <Alert variant="destructive">
            <AlertDescription>
              <p>{error ?? RELAXMATE_UNANSWERED}</p>
              {unanswered ? (
                <div className="mt-2">
                  <Button variant="outline" onClick={() => send(true)}>
                    Coba kirim ulang
                  </Button>
                </div>
              ) : null}
            </AlertDescription>
          </Alert>
        ) : null}

        {!consentDone ? (
          <Alert>
            <AlertDescription>
              <p>{RELAXMATE_CONSENT_NEEDED}</p>
              <div className="mt-2">
                <Button onClick={() => setConsentOpen(true)}>Buka persetujuan</Button>
              </div>
            </AlertDescription>
          </Alert>
        ) : null}

        {consentDone && limitReached ? (
          <Alert>
            <AlertDescription>
              <p>{RELAXMATE_DAILY_LIMIT}</p>
              <a href="/konsultasi" className="mt-1 inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2">
                Konsultasi Profesional
              </a>
            </AlertDescription>
          </Alert>
        ) : null}

        <ChatInput
          value={draft}
          onChange={setDraft}
          onSend={() => send(false)}
          maxChars={limits.message_max_chars}
          disabled={!canType || sending}
          textareaRef={inputRef}
        />
      </div>

      <ConsentDialog
        open={consentOpen}
        onDecline={() => router.visit('/dashboard')}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
