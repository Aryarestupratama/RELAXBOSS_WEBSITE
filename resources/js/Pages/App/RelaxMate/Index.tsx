import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { ArrowRight, MessageCircle, Plus } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import ConsentDialog from '@/Components/shared/ConsentDialog';
import EmptyState from '@/Components/shared/EmptyState';
import { Button } from '@/Components/ui/button';
import { needsConsent, type SharedConsentProps } from '@/lib/consent';
import { RELAXMATE_EMPTY, RELAXMATE_INTRO } from '@/lib/relaxmate';

type ConversationItem = {
  id: string;
  label: string;
  total_turns: number;
};

type IndexProps = {
  conversations: ConversationItem[];
  prev_page_url: string | null;
  next_page_url: string | null;
};

/** SCR-017 (daftar Percakapan). Tanpa judul: hanya tanggal dan jam. */
export default function Index({ conversations, prev_page_url, next_page_url }: IndexProps) {
  const consent = usePage<SharedConsentProps>().props.consent ?? null;
  const [consentOpen, setConsentOpen] = useState(needsConsent(consent));
  const [starting, setStarting] = useState(false);

  // Belum consent: tombol membuka dialog (aksi AI akan ditolak server tanpa consent).
  const start = () => {
    if (needsConsent(consent)) {
      setConsentOpen(true);
      return;
    }
    router.post('/app/relaxmate/percakapan', {}, {
      onStart: () => setStarting(true),
      onFinish: () => setStarting(false),
    });
  };

  const startButton = (
    <Button onClick={start} disabled={starting}>
      <Plus aria-hidden="true" />
      Mulai percakapan baru
    </Button>
  );

  return (
    <AppShell title="RelaxMate">
      <h1>RelaxMate</h1>
      <p className="mt-2 text-text-secondary">{RELAXMATE_INTRO}</p>

      <div className="mt-6">
        {conversations.length === 0 ? (
          <EmptyState icon={MessageCircle} message={RELAXMATE_EMPTY} action={startButton} />
        ) : (
          <>
            <div className="mb-4">{startButton}</div>
            <h2 className="text-lg font-semibold">Percakapanmu</h2>
            <ul className="mt-3 grid gap-3">
              {conversations.map((conversation) => (
                <li key={conversation.id}>
                  <Link
                    href={`/app/relaxmate/${conversation.id}`}
                    className="flex min-h-11 items-center justify-between gap-3 rounded-xl border border-border bg-card p-4 shadow-card transition-colors hover:bg-neutral-soft"
                  >
                    <span className="min-w-0">
                      <span className="block font-medium">{conversation.label}</span>
                      <span className="block text-sm text-text-secondary">{conversation.total_turns} balasan</span>
                    </span>
                    <ArrowRight className="size-5 shrink-0" aria-hidden="true" />
                  </Link>
                </li>
              ))}
            </ul>

            {prev_page_url || next_page_url ? (
              <div className="mt-4 flex justify-between gap-3">
                {prev_page_url ? (
                  <Button asChild variant="outline">
                    <Link href={prev_page_url}>Sebelumnya</Link>
                  </Button>
                ) : (
                  <span />
                )}
                {next_page_url ? (
                  <Button asChild variant="outline">
                    <Link href={next_page_url}>Berikutnya</Link>
                  </Button>
                ) : null}
              </div>
            ) : null}
          </>
        )}
      </div>

      <ConsentDialog
        open={consentOpen}
        onDecline={() => router.visit('/dashboard')}
        onComplete={() => setConsentOpen(false)}
      />
    </AppShell>
  );
}
