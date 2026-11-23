/**
 * Ruang tulis (TASK-052, revisi TASK-054): perhitungan murni dan pengukuran DOM untuk kertas yang menulis seperti mesin tik.
 * Posisi tempat huruf berikutnya dicetak diukur lewat salinan tak terlihat dari teks (`measureCaret`), dan kertas
 * "naik" sendiri tanpa batang gulir (`paperOffset`): baris aktif berhenti di jangkar, kertas yang bergeser.
 */

/** Letak baris aktif di dalam kertas yang terlihat, sebagai bagian tinggi (0 = atas, 1 = bawah). */
export const ANCHOR_RATIO = 0.42;

export type CaretPoint = {
  /** Tepi kiri tempat huruf berikutnya dicetak (tepat setelah huruf terakhir sebelum kursor). */
  x: number;
  /** Atas baris aktif (kelipatan tinggi baris dari atas isi kertas), bukan atas kotak huruf. */
  y: number;
};

type OffsetInput = {
  /** Atas baris aktif, dihitung dari atas isi kertas. */
  lineTop: number;
  viewportHeight: number;
  anchorRatio?: number;
};

/**
 * Berapa piksel kertas harus naik supaya baris aktif ada di jangkar. Selama baris aktif masih di atas jangkar
 * kertas diam (0); begitu tulisan turun melewatinya, kertas naik satu baris demi satu baris. Bila kursor dipindah
 * ke baris yang lebih atas, nilainya mengecil sehingga kertas turun kembali. Tidak pernah negatif.
 */
export function paperOffset({ lineTop, viewportHeight, anchorRatio = ANCHOR_RATIO }: OffsetInput): number {
  return Math.max(0, lineTop - viewportHeight * anchorRatio);
}

/**
 * Posisi tempat huruf berikutnya dicetak (piksel, relatif terhadap sudut kiri atas isi kertas). [mirror] harus
 * `position: absolute` dan bergaya sama dengan [text] (font, padding, pembungkusan), sehingga garis yang membungkus
 * identik. `y` dibulatkan ke baris (bukan atas kotak huruf yang bergantung pada font) agar sejajar dengan garis buku.
 */
export function measureCaret(text: HTMLTextAreaElement, mirror: HTMLDivElement): CaretPoint {
  const position = text.selectionStart ?? text.value.length;
  mirror.style.width = `${text.clientWidth}px`;
  mirror.textContent = text.value.slice(0, position);
  const marker = document.createElement('span');
  // Spasi lebar nol: memberi baris kosong tinggi dan posisi tanpa menambah lebar.
  marker.textContent = '\u200b';
  mirror.appendChild(marker);

  const style = getComputedStyle(text);
  const lineHeight = Number.parseFloat(style.lineHeight);
  const padTop = Number.parseFloat(style.paddingTop) || 0;
  if (Number.isNaN(lineHeight) || lineHeight <= 0) {
    return { x: marker.offsetLeft, y: marker.offsetTop };
  }
  const row = Math.max(0, Math.floor((marker.offsetTop + marker.offsetHeight / 2 - padTop) / lineHeight));
  return { x: marker.offsetLeft, y: padTop + row * lineHeight };
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

const monthFormat = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric', timeZone: 'UTC' });

/** "November 2026" dari tanggal WIB `Y-m-d` (diurai sebagai UTC supaya tidak bergeser hari). */
export function monthLabel(date: string): string {
  return monthFormat.format(new Date(`${date}T00:00:00Z`));
}
