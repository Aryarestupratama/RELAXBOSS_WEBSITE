import type { JournalEntryData } from '@/lib/mood';
import { moodImage } from '@/lib/mood';
import { writingStamp } from '@/lib/writing';

type JournalSheetProps = {
  entry: JournalEntryData;
  /** Arah perpindahan yang baru saja terjadi; menentukan dari sisi mana halaman masuk. */
  direction: 'older' | 'newer' | null;
};

/**
 * CMP-050. Satu halaman buku harian untuk dibaca: kepala (tanggal, jam WIB, tenaga, tanda "diedit", stiker mood) dan isi
 * catatan dalam tulisan tangan di atas garis buku, sama dengan kertas Ruang tulis (CMP-048). Catatan panjang digulir di
 * dalam halaman (dapat difokus dengan keyboard). Isi tidak dapat diubah di sini; ubah lewat Ruang tulis.
 */
export default function JournalSheet({ entry, direction }: JournalSheetProps) {
  const stamp = writingStamp(new Date(entry.logged_at));
  const detail = [`${stamp.time} WIB`, entry.arousal_label, entry.edited ? 'diedit' : null].filter(Boolean).join(' · ');

  return (
    <article
      aria-label={`Catatan ${stamp.date}`}
      data-direction={direction ?? undefined}
      className="reading-paper rounded-2xl border-[1.5px] border-border p-5"
    >
      <header className="mb-4 flex items-center justify-between gap-3">
        <div className="min-w-0">
          <h2 className="text-sm font-semibold text-text">{stamp.date}</h2>
          <p className="text-[0.8125rem] text-text-secondary">{detail}</p>
        </div>
        <img src={moodImage(entry.mood)} width={48} height={48} alt={entry.mood_label} className="size-12 shrink-0 object-contain" />
      </header>
      <div
        tabIndex={0}
        role="region"
        aria-label="Isi catatan"
        className="reading-text max-h-[min(30rem,55dvh)] overflow-y-auto rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
      >
        {entry.note}
      </div>
    </article>
  );
}
