import { useEffect, useRef, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@/Layouts/AppShell';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import ConversationPanel, {
  StartConversationButton,
  type ConversationItem,
} from '@/Components/shared/ConversationPanel';
import Mascot from '@/Components/shared/Mascot';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { MASCOT_LEAVE_MS, type MascotName } from '@/lib/mascot';
import { markMascotEntrance } from '@/lib/mascotEntrance';
import { RELAXMATE_INTRO } from '@/lib/relaxmate';
import { useStartConversation } from '@/lib/useStartConversation';

type IndexProps = {
  conversations: ConversationItem[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

/**
 * SCR-017 (daftar Percakapan). Dua panel: daftar di kiri (tanggal dan jam, tanpa judul), sambutan dengan maskot di kanan.
 * Maskot: diam (idle), menyimak saat tombol mulai disorot atau difokus, berpikir saat Percakapan sedang dibuat.
 */
export default function Index({ conversations, prev_page_url, next_page_url }: IndexProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const [consentOpen, setConsentOpen] = useState(needsConsent(consent));
  const [attention, setAttention] = useState(false);
  const [leaving, setLeaving] = useState(false);
  const timer = useRef<number | undefined>(undefined);

  useEffect(() => () => window.clearTimeout(timer.current), []);

  // Permintaan gagal dan halaman tetap di sini: maskot kembali.
  const wasStarting = useRef(false);

  /** Maskot berjalan keluar lebih dulu, baru pindah ke Percakapan (langsung bila gerak dikurangi). */
  const leaveThen = (go: () => void): void => {
    if (leaving) {
      return;
    }
    markMascotEntrance();
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      go();
      return;
    }
    setLeaving(true);
    timer.current = window.setTimeout(go, MASCOT_LEAVE_MS);
  };

  const { starting, start } = useStartConversation(consent, () => setConsentOpen(true), leaveThen);

  useEffect(() => {
    if (wasStarting.current && !starting) {
      setLeaving(false);
    }
    wasStarting.current = starting;
  }, [starting]);

  const mascot: MascotName = leaving ? 'walk' : starting ? 'thinking' : attention ? 'listening' : 'idle';
  const startProps = {
    onMouseEnter: () => setAttention(true),
    onMouseLeave: () => setAttention(false),
    onFocus: () => setAttention(true),
    onBlur: () => setAttention(false),
  };

  return (
    <AppShell title="RelaxMate" wide staggered>
      <div className="flex flex-col gap-4 lg:flex-row">
        <div data-load className="order-2 lg:order-1 lg:w-80 lg:shrink-0">
          <ConversationPanel
            conversations={conversations}
            prevUrl={prev_page_url}
            nextUrl={next_page_url}
            starting={starting}
            onStart={start}
            startProps={startProps}
            onOpen={(href) => leaveThen(() => router.visit(href))}
            className="lg:h-[clamp(28rem,calc(100dvh_-_9rem),36rem)]"
          />
        </div>

        <section
          data-load
          className="order-1 flex min-w-0 flex-1 flex-col items-center justify-center rounded-3xl bg-card px-6 py-8 text-center shadow-lift lg:order-2 lg:h-[clamp(28rem,calc(100dvh_-_9rem),36rem)] lg:px-8"
        >
          <div aria-hidden="true" className="relative mb-1 h-45 w-47 overflow-hidden md:h-64 md:w-64">
            <div className="absolute inset-x-2 bottom-0 h-32 rounded-[58%_42%_52%_48%/55%_50%_50%_45%] bg-soft-lavender md:h-45" />
            <div className="absolute bottom-1.5 left-1/2 h-3.5 w-32.5 -translate-x-1/2 rounded-full bg-primary/15 blur-[2px]" />
            <Mascot
              state={mascot}
              walkOutMs={MASCOT_LEAVE_MS}
              className="absolute bottom-3 left-1/2 -translate-x-1/2 [--s:0.36] md:bottom-3.5 md:[--s:0.52]"
            />
          </div>
          <h1 className="mt-2 text-2xl leading-tight font-bold tracking-tight md:text-[1.75rem]">RelaxMate</h1>
          <p className="mt-2 max-w-md text-sm leading-relaxed text-text-secondary md:text-base">{RELAXMATE_INTRO}</p>
          <StartConversationButton starting={starting} onClick={start} className="mt-5" {...startProps} />
        </section>
      </div>

      <ConsentDialog
        open={consentOpen}
        onDecline={() => router.visit('/dashboard')}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
