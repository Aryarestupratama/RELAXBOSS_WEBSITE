import { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import ConversationPanel, {
  StartConversationButton,
  type ConversationItem,
} from '@/Components/shared/ConversationPanel';
import { Card } from '@/Components/ui/card';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { RELAXMATE_INTRO, RELAXMATE_NEW } from '@/lib/relaxmate';
import { useStartConversation } from '@/lib/useStartConversation';

type IndexProps = {
  conversations: ConversationItem[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

/**
 * SCR-017 (daftar Percakapan). Dua panel: daftar di kiri (tanggal dan jam, tanpa judul), sambutan di kanan
 * dengan ilustrasi setengah badan RelaxMate (gambar statis) di atas alas `--soft-lavender`.
 */
export default function Index({ conversations, prev_page_url, next_page_url }: IndexProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const [consentOpen, setConsentOpen] = useState(needsConsent(consent));
  const { starting, start } = useStartConversation(consent, () => setConsentOpen(true));

  return (
    <AppShell title="RelaxMate" wide staggered>
      <PageHeader title="RelaxMate" description={RELAXMATE_INTRO} />

      <div className="flex flex-col gap-4 lg:flex-row">
        <div data-load className="order-2 lg:order-1 lg:w-80 lg:shrink-0">
          <ConversationPanel
            conversations={conversations}
            prevUrl={prev_page_url}
            nextUrl={next_page_url}
            starting={starting}
            onStart={start}
            className="lg:h-[clamp(26rem,calc(100dvh_-_15rem),36rem)]"
          />
        </div>

        <Card
          data-load
          role="region"
          aria-label="Mulai bercerita"
          className="order-1 min-w-0 flex-1 items-center justify-center gap-0 px-6 py-8 text-center lg:order-2 lg:h-[clamp(26rem,calc(100dvh_-_15rem),36rem)] lg:px-8"
        >
          <div aria-hidden="true" className="relative h-44 w-44 overflow-hidden rounded-3xl bg-soft-lavender md:h-52 md:w-48">
            <img
              src="/images/relaxmate/relaxmate-half.webp"
              width={476}
              height={660}
              alt=""
              className="absolute bottom-0 left-1/2 h-[94%] w-auto max-w-none -translate-x-1/2"
            />
          </div>
          <p className="mt-4 max-w-md text-base leading-relaxed text-text md:text-lg">{RELAXMATE_NEW}</p>
          <StartConversationButton starting={starting} onClick={start} className="mt-5" />
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
