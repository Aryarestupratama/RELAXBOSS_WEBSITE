import { Asterisk } from 'lucide-react';

/** Penanda kolom wajib: ikon bintang untuk mata, teks "(wajib)" untuk pembaca layar. */
export default function RequiredMark() {
  return (
    <>
      <Asterisk className="ml-0.5 inline size-3.5 align-top text-destructive" aria-hidden="true" />
      <span className="sr-only"> (wajib)</span>
    </>
  );
}
