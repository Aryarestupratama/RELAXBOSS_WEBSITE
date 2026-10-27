import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowUpRight, type LucideIcon } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { NotchCorner } from '@/Components/ui/notch-corner';

type CardTone = 'lavender' | 'sky' | 'mint' | 'peach';

type ActionTileProps = {
  icon: LucideIcon;
  title: string;
  description: string;
  href: string;
  /** Warna pastel kartu (teks di atasnya selalu --text, RULE-052). */
  tone: CardTone;
  /** Isi tambahan di bawah deskripsi, mis. lencana. */
  children?: ReactNode;
  /** Tujuan adalah halaman Blade (bukan Inertia, mis. /artikel): pakai <a> biasa agar kunjungan penuh. */
  native?: boolean;
};

/**
 * Kartu aksi menuju fitur (shadcn Card), satu bahasa dengan kartu fitur Beranda: sudut membulat besar dan
 * sudut kanan bawah dipotong takik (kelas `notch-cut`) dengan cakram panah biru gelap (NotchCorner).
 * Seluruh kartu adalah satu tautan (`native` untuk halaman Blade). Dipakai Dashboard (SCR-011) dan katalog Asesmen (SCR-012).
 */
export default function ActionTile({ icon: Icon, title, description, href, tone, children, native = false }: ActionTileProps) {
  const Anchor = native ? 'a' : Link;

  return (
    <Anchor href={href} data-load data-lift data-press className="group relative block h-full">
      <div className="drop-lift h-full group-hover:drop-lift-hover">
        <Card tone={tone} className="notch-cut h-full min-h-36 gap-1 p-4 pr-16 shadow-none!">
          <CardHeader className="gap-0">
            <span className="grid size-10 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
              <Icon className="size-5" />
            </span>
            <CardTitle className="mt-3 text-base font-bold">{title}</CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription className="line-clamp-2 text-[0.8125rem] leading-relaxed">{description}</CardDescription>
          </CardContent>
          {children ? <CardContent className="mt-2 flex flex-wrap items-center gap-1.5">{children}</CardContent> : null}
        </Card>
      </div>
      <NotchCorner>
        <ArrowUpRight className="size-5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
      </NotchCorner>
    </Anchor>
  );
}
