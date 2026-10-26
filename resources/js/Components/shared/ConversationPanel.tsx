import type { ComponentProps, MouseEvent } from 'react';
import { Link } from '@inertiajs/react';
import { ChevronRight, LoaderCircle, Plus } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { cn } from '@/lib/utils';
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
    <Button data-press disabled={starting || props.disabled} className={`rounded-full! px-5 text-sm ${className}`} {...props}>
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
    <Card role="region" aria-labelledby="daftar-percakapan" className={cn('gap-0', className)}>
      <CardHeader className="px-5 pt-5 pb-3">
        <CardTitle id="daftar-percakapan" role="heading" aria-level={2} className="text-base">
          Percakapanmu
        </CardTitle>
      </CardHeader>
      <Separator />

      {conversations.length === 0 ? (
        <div className="flex flex-1 flex-col items-center justify-center gap-4 px-6 pb-8 pt-2 text-center">
          <p className="text-sm text-text-secondary">{RELAXMATE_EMPTY}</p>
          <StartConversationButton starting={starting} onClick={onStart} {...startProps} />
        </div>
      ) : (
        <>
          <ul className="flex flex-1 flex-col gap-1 overflow-y-auto px-2.5 py-2">
            {conversations.map((conversation) => {
              const active = conversation.id === activeId;
              return (
                <li key={conversation.id}>
                  <Link
                    href={`/app/relaxmate/${conversation.id}`}
                    onClick={(event) => open(event, `/app/relaxmate/${conversation.id}`)}
                    aria-current={active ? 'page' : undefined}
                    className={`group flex min-h-14 items-center justify-between gap-3 rounded-2xl px-3.5 py-2.5 text-text transition-colors duration-200 ${
                      active ? 'bg-soft-lavender' : 'hover:bg-soft-lavender/50'
                    }`}
                  >
                    <span className="min-w-0">
                      <span className="block text-sm font-semibold">{conversation.label}</span>
                      <Badge variant={active ? 'outline' : 'secondary'} className="mt-1">
                        {conversation.total_turns} balasan
                      </Badge>
                    </span>
                    <ChevronRight
                      className="size-4 shrink-0 transition-transform duration-200 group-hover:translate-x-0.5"
                      aria-hidden="true"
                    />
                  </Link>
                </li>
              );
            })}
          </ul>

          <Separator />
          {prevUrl || nextUrl ? (
            <nav aria-label="Halaman daftar percakapan" className="flex gap-2 px-5 pt-3">
              {prevUrl ? (
                <Button asChild variant="outline" className="flex-1 rounded-full! px-3 text-sm">
                  <Link href={prevUrl} {...pagerProps}>
                    Sebelumnya
                  </Link>
                </Button>
              ) : (
                <span className="flex-1" />
              )}
              {nextUrl ? (
                <Button asChild variant="outline" className="flex-1 rounded-full! px-3 text-sm">
                  <Link href={nextUrl} {...pagerProps}>
                    Berikutnya
                  </Link>
                </Button>
              ) : (
                <span className="flex-1" />
              )}
            </nav>
          ) : null}

          <CardFooter className="hidden px-5 pt-3 pb-5 lg:flex">
            <StartConversationButton starting={starting} onClick={onStart} className="w-full" {...startProps} />
          </CardFooter>
        </>
      )}
    </Card>
  );
}
