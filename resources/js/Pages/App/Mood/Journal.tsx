import { useCallback, useEffect, useRef, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, PenLine, Trash2 } from 'lucide-react';
import AppShell from '@/Layouts/AppShell';
import PageHeader from '@/Components/shared/PageHeader';
import JournalSheet from '@/Components/shared/JournalSheet';
import ConfirmLeaveDialog from '@/Components/shared/ConfirmLeaveDialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import type { JournalEntryData } from '@/lib/mood';
import { monthLabel } from '@/lib/writing';

type JournalProps = {
  entries: JournalEntryData[];
  next_before: number | null;
};

/** Memuat halaman yang lebih lama saat pembaca tinggal sekian catatan dari ujung daftar. */
const PREFETCH_AHEAD = 3;

/** SCR-030. Buku harian: catatan dibaca satu halaman per catatan, terbaru dulu, dengan ubah (Ruang tulis) dan hapus. */
export default function Journal({ entries: firstPage, next_before }: JournalProps) {
  const [entries, setEntries] = useState(firstPage);
  const [nextBefore, setNextBefore] = useState(next_before);
  const [index, setIndex] = useState(0);
  const [direction, setDirection] = useState<'older' | 'newer' | null>(null);
  const [loadingMore, setLoadingMore] = useState(false);
  const [confirmDelete, setConfirmDelete] = useState(false);
  const loading = useRef(false);

  const total = entries.length;
  const entry = entries[Math.min(index, Math.max(total - 1, 0))] as JournalEntryData | undefined;

  const loadMore = useCallback(() => {
    if (nextBefore === null || loading.current) {
      return;
    }
    loading.current = true;
    setLoadingMore(true);
    router.get(
      '/app/mood/buku',
      { before: nextBefore },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['entries', 'next_before'],
        onSuccess: (page) => {
          const props = page.props as unknown as JournalProps;
          setEntries((current) => [...current, ...props.entries]);
          setNextBefore(props.next_before);
        },
        onFinish: () => {
          loading.current = false;
          setLoadingMore(false);
        },
      },
    );
  }, [nextBefore]);

  const go = useCallback(
    (target: number) => {
      if (target < 0 || target >= total) {
        return;
      }
      setDirection(target > index ? 'older' : 'newer');
      setIndex(target);
      if (target >= total - PREFETCH_AHEAD) {
        loadMore();
      }
    },
    [index, total, loadMore],
  );

  // Panah kiri dan kanan membalik halaman (kiri = lebih baru, kanan = lebih lama), kecuali saat mengetik atau dialog terbuka.
  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      const target = event.target as HTMLElement | null;
      if (confirmDelete || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) {
        return;
      }
      if (target && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName))) {
        return;
      }
      if (event.key === 'ArrowLeft') {
        go(index - 1);
      } else if (event.key === 'ArrowRight') {
        go(index + 1);
      }
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [go, index, confirmDelete]);

  const remove = () => {
    if (!entry) {
      return;
    }
    const id = entry.id;
    setConfirmDelete(false);
    router.delete(`/app/mood/${id}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        setEntries((current) => current.filter((item) => item.id !== id));
        setIndex((current) => Math.max(0, Math.min(current, total - 2)));
        setDirection(null);
      },
    });
  };

  const counter = nextBefore !== null ? `Catatan ${index + 1}` : `Catatan ${index + 1} dari ${total}`;

  return (
    <AppShell title="Buku harian" staggered>
      <PageHeader
        title="Buku harian"
        description="Catatan-catatan yang pernah kamu tulis, tersimpan seperti buku harian."
        back={{ href: '/app/mood', label: 'Mood Tracker' }}
        actions={
          <Button asChild variant="outline" className="rounded-full! text-[0.8125rem]">
            <Link href="/app/mood/tulis">
              <PenLine aria-hidden="true" />
              Tulis catatan baru
            </Link>
          </Button>
        }
      />

      {!entry ? (
        <Card data-load className="mx-auto max-w-3xl p-6">
          <CardContent className="flex flex-col items-center gap-3 text-center">
            <h2 className="text-lg font-semibold text-text">Belum ada catatan</h2>
            <p className="text-sm text-text-secondary">
              Tulis yang pertama kapan pun kamu siap. Tidak ada yang salah dengan apa pun yang kamu tulis.
            </p>
            <Button asChild className="rounded-full! text-sm">
              <Link href="/app/mood/tulis">Tulis catatan baru</Link>
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div data-load className="mx-auto flex max-w-3xl flex-col gap-4">
          <h2 className="text-base font-semibold text-text" aria-live="polite">
            {monthLabel(entry.entry_date)}
          </h2>

          <JournalSheet key={entry.id} entry={entry} direction={direction} />

          <nav aria-label="Pindah catatan" className="flex items-center justify-between gap-3">
            <Button
              type="button"
              variant="outline"
              className="rounded-full! text-sm"
              disabled={index <= 0}
              onClick={() => go(index - 1)}
            >
              <ArrowLeft aria-hidden="true" />
              Lebih baru
            </Button>
            <p aria-live="polite" className="text-[0.8125rem] text-text-secondary">
              {loadingMore ? 'Memuat catatan lebih lama...' : counter}
            </p>
            <Button
              type="button"
              variant="outline"
              className="rounded-full! text-sm"
              disabled={index >= total - 1}
              onClick={() => go(index + 1)}
            >
              Lebih lama
              <ArrowRight aria-hidden="true" />
            </Button>
          </nav>

          <div className="flex flex-wrap items-center justify-end gap-3">
            <Button asChild variant="outline" className="rounded-full! text-sm">
              <Link href={`/app/mood/${entry.id}/ubah`}>
                <PenLine aria-hidden="true" />
                Ubah
              </Link>
            </Button>
            <Button type="button" variant="outline" className="rounded-full! text-sm" onClick={() => setConfirmDelete(true)}>
              <Trash2 aria-hidden="true" />
              Hapus
            </Button>
          </div>
        </div>
      )}

      <ConfirmLeaveDialog
        open={confirmDelete}
        title="Hapus catatan ini?"
        description="Catatan beserta perasaan yang tercatat bersamanya akan dihapus dan tidak bisa dikembalikan."
        stayLabel="Batal"
        leaveLabel="Hapus"
        onStay={() => setConfirmDelete(false)}
        onLeave={remove}
      />
    </AppShell>
  );
}
