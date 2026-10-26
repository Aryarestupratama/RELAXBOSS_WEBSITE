import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ChatBubble from '@/Components/shared/ChatBubble';
import ChatInput from '@/Components/shared/ChatInput';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import ConversationPanel, { type ConversationItem } from '@/Components/shared/ConversationPanel';
import CrisisBanner from '@/Components/shared/CrisisBanner';
import Mascot from '@/Components/shared/Mascot';
import SuggestionCard from '@/Components/shared/SuggestionCard';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Card, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { postJson } from '@/lib/http';
import type { MascotName } from '@/lib/mascot';
import { consumeMascotEntrance } from '@/lib/mascotEntrance';
import {
  RELAXMATE_CONSENT_NEEDED,
  RELAXMATE_DAILY_LIMIT,
  RELAXMATE_NEW,
  RELAXMATE_NOTE,
  RELAXMATE_THINK_MS,
  RELAXMATE_TYPING,
  RELAXMATE_UNANSWERED,
  type ChatMessage,
  type SendResponse,
} from '@/lib/relaxmate';
import { useStartConversation } from '@/lib/useStartConversation';

type ShowProps = {
  conversations: ConversationItem[];
  prev_page_url: string | null;
  next_page_url: string | null;
  conversation: { id: string; label: string; has_crisis: boolean };
  messages: ChatMessage[];
  unanswered: boolean;
  limits: { message_max_chars: number; per_day: number; remaining_today: number };
};

/**
 * SCR-017 (Percakapan). Kiri: daftar Percakapan (>= 1024px). Kanan dibagi dua (>= 1280px): panel maskot besar
 * dan obrolan. Di bawah 1280px maskot jadi wajah kecil di kepala obrolan.
 * Pesan dikirim lewat fetch JSON (API-015); riwayat dari server. Maskot menyimak saat pengguna mengetik
 * dan berpikir selama balasan ditunggu.
 */
export default function Show({
  conversations,
  prev_page_url,
  next_page_url,
  conversation,
  messages: initialMessages,
  unanswered: initialUnanswered,
  limits,
}: ShowProps) {
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
  // Balasan yang sedang "diucapkan" (diketik bertahap); maskot berbicara selama ini.
  const [speakingId, setSpeakingId] = useState<number | string | null>(null);

  // Dibuka lewat klik di daftar: maskot di panel besar masuk dulu (jalan, ambil kursi, duduk). Hanya di >= 1280px.
  const [entrance] = useState(() => consumeMascotEntrance() && window.matchMedia('(min-width: 1280px)').matches);

  const scrollRef = useRef<HTMLDivElement>(null);
  const inputRef = useRef<HTMLTextAreaElement>(null);
  const firstScroll = useRef(true);
  // Pesan yang sudah ada saat halaman dibuka tidak dianimasikan; hanya balasan baru.
  const initialIds = useRef(new Set<number | string>(initialMessages.map((m) => m.id)));

  const { starting, start } = useStartConversation(consent, () => setConsentOpen(true));

  // Sinkron ulang bila server mengirim data baru (mis. setelah muat ulang).
  useEffect(() => {
    setMessages(initialMessages);
    setUnanswered(initialUnanswered);
    setHasCrisis(conversation.has_crisis);
    setRemaining(limits.remaining_today);
  }, [initialMessages, initialUnanswered, conversation.has_crisis, limits.remaining_today]);

  // Gulir kotak pesan (bukan halaman) ke bawah. Pertama kali langsung, selanjutnya halus.
  useEffect(() => {
    const el = scrollRef.current;
    if (!el) {
      return;
    }
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    el.scrollTo({ top: el.scrollHeight, behavior: reduce || firstScroll.current ? 'auto' : 'smooth' });
    firstScroll.current = false;
  }, [messages.length, sending]);

  const consentDone = consent?.complete ?? false;
  const limitReached = remaining <= 0;
  const canType = consentDone && !limitReached;
  const lastAssistantIndex = messages.map((m) => m.role).lastIndexOf('assistant');
  const mascot: MascotName = sending
    ? 'thinking'
    : speakingId !== null
      ? 'speaking'
      : draft.trim() !== ''
        ? 'listening'
        : 'idle';

  const scrollToEnd = () => {
    const el = scrollRef.current;
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  };

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
    setSpeakingId(null);
    const startedAt = Date.now();

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

    // Jeda minimal supaya animasi berpikir terlihat walau balasan datang cepat.
    if (result.ok) {
      const wait = RELAXMATE_THINK_MS - (Date.now() - startedAt);
      if (wait > 0) {
        await new Promise((resolve) => setTimeout(resolve, wait));
      }
    }
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
      setSpeakingId(assistant_message.id);
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
    <AppShell title="RelaxMate" wide staggered fill>
      <Link
        href="/app/relaxmate"
        className="mb-1 inline-flex min-h-11 shrink-0 items-center gap-2 self-start text-[0.8125rem] font-medium text-text-secondary transition-colors hover:text-text lg:hidden"
      >
        <ArrowLeft className="size-4" aria-hidden="true" />
        Semua percakapan
      </Link>

      {/* Tinggi dari AppShell `fill`: baris ini mengisi sisa layar, tidak ada tinggi tetap. Yang digulir hanya daftar dan riwayat pesan. */}
      <div className="flex min-h-0 flex-1 flex-col gap-4 lg:flex-row">
        <div data-load className="hidden min-h-0 lg:flex lg:w-80 lg:shrink-0">
          <ConversationPanel
            conversations={conversations}
            activeId={conversation.id}
            prevUrl={prev_page_url}
            nextUrl={next_page_url}
            starting={starting}
            onStart={start}
            listOnlyReload
            className="min-h-0 w-full"
          />
        </div>

        {/* Satu kartu: satu kepala, lalu maskot (kiri, >= 1280px) dan obrolan (kanan). Di bawah 1280px maskot jadi wajah kecil di kepala. */}
        <Card
          data-load
          role="region"
          aria-label="Percakapan dengan RelaxMate"
          className="min-h-0 min-w-0 flex-1 gap-0 overflow-hidden"
        >
          {/* Satu kepala untuk maskot dan obrolan */}
          <CardHeader className="flex-row items-center gap-3 px-4 py-3 md:px-5">
            <span
              aria-hidden="true"
              className="relative size-12 shrink-0 overflow-hidden rounded-full bg-soft-lavender xl:hidden"
            >
              <Mascot state={mascot} className="absolute -left-2 -top-1 [--s:0.19]" />
            </span>
            <div className="min-w-0">
              <CardTitle role="heading" aria-level={1} className="text-base leading-tight font-bold md:text-lg">
                RelaxMate
              </CardTitle>
              <p className="text-[0.8125rem] text-text-secondary">{conversation.label}</p>
            </div>
          </CardHeader>
          <Separator />

          <div className="flex min-h-0 flex-1 flex-col xl:flex-row">
            <div
              data-mascot-stage
              className="hidden w-80 shrink-0 items-center justify-center overflow-hidden bg-soft-lavender px-5 xl:flex"
            >
              <div aria-hidden="true" className="relative h-64 w-64 max-w-full">
                <div className="absolute inset-x-2 bottom-0 h-45 rounded-[58%_42%_52%_48%/55%_50%_50%_45%] bg-card/50" />
                <div className="absolute bottom-1.5 left-1/2 h-3.5 w-32.5 -translate-x-1/2 rounded-full bg-primary/15 blur-[2px]" />
                <Mascot state={mascot} seated entrance={entrance} className="absolute bottom-0 left-1/2 -translate-x-1/2 [--s:0.62]" />
              </div>
            </div>

          <div className="flex min-h-0 min-w-0 flex-1 flex-col">
            {hasCrisis ? (
              <div id="bantuan" tabIndex={-1} className="max-h-[45%] shrink-0 overflow-y-auto p-3 outline-none md:px-5">
                <CrisisBanner />
              </div>
            ) : null}

            <div
              ref={scrollRef}
              tabIndex={0}
              aria-label="Riwayat pesan"
              className="flex flex-1 flex-col overflow-y-auto bg-neutral-soft/40 p-3 md:p-4"
            >
              {messages.length === 0 && !sending ? (
                <div className="m-auto flex flex-col items-center text-center">
                  <Mascot state={mascot} className="[--s:0.4] xl:hidden" />
                  <p className="mt-2 max-w-xs text-sm text-text-secondary">{RELAXMATE_NEW}</p>
                </div>
              ) : null}

              <ol role="log" aria-live="polite" aria-label="Percakapan" className="flex flex-col gap-3">
                {messages.map((message, index) => {
                  const suggestion = message.suggestion;
                  const speaking = message.id === speakingId;
                  const visible =
                    suggestion !== null &&
                    !speaking &&
                    !dismissed.has(message.id) &&
                    (suggestion.priority === 1 || index === lastAssistantIndex);

                  return (
                    <ChatBubble
                      key={message.id}
                      message={message}
                      animate={message.role === 'assistant' && !initialIds.current.has(message.id)}
                      typewriter={speaking}
                      onTypingTick={scrollToEnd}
                      onTypingDone={() => setSpeakingId((current) => (current === message.id ? null : current))}
                    >
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
                  <li className="flex items-start motion-safe:animate-in motion-safe:fade-in motion-safe:duration-300">
                    <p
                      role="status"
                      className="flex items-center gap-3 rounded-2xl rounded-bl-md bg-card px-3.5 py-2.5 text-sm text-text-secondary shadow-card"
                    >
                      <span className="flex items-center gap-1.5" aria-hidden="true">
                        <span className="size-2 rounded-full bg-brand motion-safe:animate-pulse" />
                        <span className="size-2 rounded-full bg-brand motion-safe:animate-pulse [animation-delay:200ms]" />
                        <span className="size-2 rounded-full bg-brand motion-safe:animate-pulse [animation-delay:400ms]" />
                      </span>
                      {RELAXMATE_TYPING}
                    </p>
                  </li>
                ) : null}
              </ol>
            </div>

            <Separator />
            <div className="grid grid-cols-[minmax(0,1fr)] gap-2.5 p-3 md:px-5 md:py-4">
              {(error || unanswered) && !sending ? (
                <Alert variant="destructive">
                  <AlertDescription>
                    <p>{error ?? RELAXMATE_UNANSWERED}</p>
                    {unanswered ? (
                      <div className="mt-2">
                        <Button variant="outline" className="rounded-full! px-5 text-sm" onClick={() => send(true)}>
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
                      <Button className="rounded-full! px-5 text-sm" onClick={() => setConsentOpen(true)}>
                        Buka persetujuan
                      </Button>
                    </div>
                  </AlertDescription>
                </Alert>
              ) : null}

              {consentDone && limitReached ? (
                <Alert>
                  <AlertDescription>
                    <p>{RELAXMATE_DAILY_LIMIT}</p>
                    <a
                      href="/konsultasi"
                      className="mt-1 inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2"
                    >
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

              <p className="text-center text-xs text-text-secondary">
                {RELAXMATE_NOTE} Butuh bantuan sekarang?{' '}
                <a href="/konsultasi" className="font-medium text-brand-strong underline underline-offset-2">
                  Konsultasi Profesional
                </a>
              </p>
            </div>
          </div>
          </div>
        </Card>
      </div>

      <ConsentDialog
        open={consentOpen}
        onDecline={() => router.visit('/dashboard')}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
