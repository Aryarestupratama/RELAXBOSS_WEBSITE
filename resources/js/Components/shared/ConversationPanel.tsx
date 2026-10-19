import type { ComponentProps, MouseEvent } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, LoaderCircle, Plus } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { RELAXMATE_EMPTY } from '@/lib/relaxmate';

export type ConversationItem = {
  id: string;
  label: string;
  total_turns: number;
};

type StartButtonProps = Omit<ComponentProps<typeof Button>, 'children'> & {
  starting: boolean;
};

/** Tombol "Mulai percakapan baru". Saat `starting`: nonaktif dan "Memproses..." (mencegah klik ganda). */
export function StartConversationButton({ starting, className = '', ...props }: StartButtonProps) {
  return (
    <Button data-press disabled={starting || props.disabled} className={className} {...props}>
      {starting ? (
        <LoaderCircle className="motion-safe:animate-spin" aria-hidden="true" />
      ) : (
        <Plus aria-hidden="true" />
      )}
      {starting ? 'Memproses...' : 'Mulai percakapan baru'}
    </Button>
  );
}

type ConversationPanelProps = {
  conversations: ConversationItem[];
  /** Percakapan yang sedang dibuka (halaman Percakapan); kosong di halaman daftar. */
  activeId?: string;
  prevUrl: string | null;
  nextUrl: string | null;
  starting: boolean;
  onStart: () => void;
  /** Penanda untuk menyimak: dipasang ke tombol mulai (maskot menyimak saat tombol disorot). */
  startProps?: Pick<ComponentProps<'button'>, 'onMouseEnter' | 'onMouseLeave' | 'onFocus' | 'onBlur'>;
  /** Paginasi di halaman Percakapan hanya memuat ulang daftar, bukan pesan. */
  listOnlyReload?: boolean;
  /** Klik biasa pada baris: induk boleh menunda perpindahan (mis. animasi maskot). Klik dengan tombol pengubah tidak dicegat. */
  onOpen?: (href: string) => void;
  className?: string;
};

const LIST_PROPS = ['conversations', 'prev_page_url', 'next_page_url'];

/**
 * Panel "Percakapanmu": daftar Percakapan (tanggal dan jam, tanpa judul), paginasi, dan tombol mulai.
 * Tombol mulai di dasar panel hanya tampil di layar >= 1024px; di HP tombol ada di panel sambutan.
 */
export default function ConversationPanel({
  conversations,
  activeId,
  prevUrl,
  nextUrl,
  starting,
  onStart,
  startProps,
  listOnlyReload = false,
  onOpen,
  className = '',
}: ConversationPanelProps) {
  const open = (event: MouseEvent, href: string): void => {
    if (!onOpen || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) {
      return;
    }
    event.preventDefault();
    onOpen(href);
  };
  const pagerProps = listOnlyReload ? { only: LIST_PROPS, preserveState: true, preserveScroll: true } : {};

  return (
    <section
      aria-labelledby="daftar-percakapan"
      className={`flex flex-col rounded-xl border border-border bg-card shadow-card ${className}`}
    >
      <h2 id="daftar-percakapan" className="px-6 pb-3 pt-6 text-xl">
        Percakapanmu
      </h2>

      {conversations.length === 0 ? (
        <div className="flex flex-1 flex-col items-center justify-center gap-5 px-8 pb-10 pt-4 text-center">
          <p className="text-text-secondary">{RELAXMATE_EMPTY}</p>
          <StartConversationButton starting={starting} onClick={onStart} {...startProps} />
        </div>
      ) : (
        <>
          <ul className="flex flex-1 flex-col gap-1 overflow-y-auto px-3 py-1">
            {conversations.map((conversation) => {
              const active = conversation.id === activeId;
              return (
                <li key={conversation.id}>
                  <Link
                    href={`/app/relaxmate/${conversation.id}`}
                    onClick={(event) => open(event, `/app/relaxmate/${conversation.id}`)}
                    aria-current={active ? 'page' : undefined}
                    className={`flex min-h-18 items-center justify-between gap-3 rounded-xl px-4 py-3 text-text transition-colors ${
                      active ? 'bg-brand-soft' : 'hover:bg-neutral-soft'
                    }`}
                  >
                    <span className="min-w-0">
                      <span className="block font-medium">{conversation.label}</span>
                      <span className={`block text-sm ${active ? 'text-text' : 'text-text-secondary'}`}>
                        {conversation.total_turns} balasan
                      </span>
                    </span>
                    <ChevronRight className="size-5 shrink-0" aria-hidden="true" />
                  </Link>
                </li>
              );
            })}
          </ul>

          {prevUrl || nextUrl ? (
            <nav aria-label="Halaman daftar percakapan" className="flex gap-2 px-6 pb-2 pt-3">
              {prevUrl ? (
                <Button asChild variant="outline" className="flex-1 px-3">
                  <Link href={prevUrl} {...pagerProps}>
                    Sebelumnya
                  </Link>
                </Button>
              ) : (
                <span className="flex-1" />
              )}
              {nextUrl ? (
                <Button asChild variant="outline" className="flex-1 px-3">
                  <Link href={nextUrl} {...pagerProps}>
                    Berikutnya
                  </Link>
                </Button>
              ) : (
                <span className="flex-1" />
              )}
            </nav>
          ) : null}

          <div className="hidden px-6 pb-6 pt-3 lg:block">
            <StartConversationButton starting={starting} onClick={onStart} className="w-full" {...startProps} />
          </div>
        </>
      )}
    </section>
  );
}
