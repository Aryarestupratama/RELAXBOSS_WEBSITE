/**
 * Ruang tulis (TASK-052): perhitungan murni dan pengukuran DOM untuk kertas yang menulis seperti mesin tik.
 * Posisi huruf terakhir diukur lewat salinan tak terlihat dari teks (`measureCaret`), dan kertas "naik"
 * (gulir) menjaga baris aktif di sekitar jangkar, bukan di dasar layar (`riseTarget`).
 */

/** Letak baris aktif di dalam kertas yang terlihat, sebagai bagian tinggi (0 = atas, 1 = bawah). */
export const ANCHOR_RATIO = 0.42;

export type CaretPoint = { x: number; y: number };

type RiseInput = {
  /** Atas baris aktif, dihitung dari atas isi kertas. */
  lineTop: number;
  lineHeight: number;
  /** Gulir kertas saat ini dan tinggi bagian yang terlihat. */
  scrollTop: number;
  viewportHeight: number;
  anchorRatio?: number;
};

/**
 * Posisi gulir baru supaya baris aktif berada di jangkar, atau `null` bila kertas tidak perlu bergeser.
 * Baris pertama yang masih di atas jangkar tidak menggeser apa pun; begitu tulisan turun melewati jangkar,
 * kertas naik satu baris demi satu baris. Baris di atas layar (mis. kursor dipindah ke atas) ditarik kembali.
 */
export function riseTarget({ lineTop, lineHeight, scrollTop, viewportHeight, anchorRatio = ANCHOR_RATIO }: RiseInput): number | null {
  const anchor = viewportHeight * anchorRatio;
  const offset = lineTop - scrollTop;
  if (offset < 0 || offset > anchor + lineHeight / 2) {
    return Math.max(0, lineTop - anchor);
  }
  return null;
}

/**
 * Posisi kursor (piksel, relatif terhadap sudut kiri atas isi kertas). [mirror] harus `position: absolute`
 * dan bergaya sama dengan [text] (font, padding, pembungkusan), sehingga garis yang membungkus identik.
 */
export function measureCaret(text: HTMLTextAreaElement, mirror: HTMLDivElement): CaretPoint {
  const position = text.selectionStart ?? text.value.length;
  mirror.style.width = `${text.clientWidth}px`;
  mirror.textContent = text.value.slice(0, position);
  const marker = document.createElement('span');
  // Spasi lebar nol: memberi baris kosong tinggi dan posisi tanpa menambah lebar.
  marker.textContent = '\u200b';
  mirror.appendChild(marker);
  return { x: marker.offsetLeft, y: marker.offsetTop };
}

const dateFormat = new Intl.DateTimeFormat('id-ID', {
  weekday: 'long',
  day: 'numeric',
  month: 'long',
  year: 'numeric',
  timeZone: 'Asia/Jakarta',
});

const timeFormat = new Intl.DateTimeFormat('id-ID', {
  hour: '2-digit',
  minute: '2-digit',
  hourCycle: 'h23',
  timeZone: 'Asia/Jakarta',
});

/** Tanggal lengkap dan jam (WIB) untuk kepala kertas. */
export function writingStamp(date: Date): { date: string; time: string } {
  return { date: dateFormat.format(date), time: timeFormat.format(date).replace(':', '.') };
}
