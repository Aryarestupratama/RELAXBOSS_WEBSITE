import type { ReactNode } from 'react';
import { Bot, Sparkles, UserRound, type LucideIcon } from 'lucide-react';
import { Card } from '@/Components/ui/card';

type MonitoringItemProps = {
  /** Judul kecil di atas isi, mis. "Pengguna" atau "RelaxMate". */
  title: string;
  /** Penanda (intent, keyakinan, krisis) sebagai teks, bukan hanya warna. */
  badges?: ReactNode;
  /** Pembicara: menentukan ikon dan nada kartu. */
  kind?: 'user' | 'assistant' | 'ai';
  /** Pesan bertanda krisis: latar `--crisis-bg`, teks tetap `--text`. */
  crisis?: boolean;
  children: ReactNode;
};

const KINDS: Record<NonNullable<MonitoringItemProps['kind']>, { icon: LucideIcon; avatar: string }> = {
  user: { icon: UserRound, avatar: 'bg-soft-sky' },
  assistant: { icon: Bot, avatar: 'bg-soft-lavender' },
  ai: { icon: Sparkles, avatar: 'bg-soft-lavender' },
};

/**
 * CMP-020. Satu butir tinjauan admin: ikon pembicara, judul, penanda, dan isi sebagai teks biasa
 * (React meng-escape, `whitespace-pre-wrap` menjaga baris baru). Hanya baca.
 */
export default function MonitoringItem({ title, badges, kind = 'assistant', crisis = false, children }: MonitoringItemProps) {
  const { icon: Icon, avatar } = KINDS[kind];

  return (
    <li>
      <Card className={`gap-3 p-4 md:p-5 ${crisis ? 'border-crisis-text/30! bg-crisis-bg!' : ''}`}>
        <div className="flex flex-wrap items-center justify-between gap-2">
          <div className="flex items-center gap-2.5">
            <span className={`grid size-8 shrink-0 place-items-center rounded-full text-text ${avatar}`} aria-hidden="true">
              <Icon className="size-4" />
            </span>
            <h3 className="text-base font-semibold">{title}</h3>
          </div>
          {badges ? <div className="flex flex-wrap gap-1.5">{badges}</div> : null}
        </div>
        <div className="leading-relaxed whitespace-pre-wrap break-words text-text">{children}</div>
      </Card>
    </li>
  );
}
