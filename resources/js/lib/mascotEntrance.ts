/**
 * Penanda bahwa halaman Percakapan dibuka lewat klik di daftar, sehingga maskot memainkan urutan masuk
 * (jalan, ambil kursi, duduk). Disimpan di memori modul: bertahan selama navigasi Inertia dan hilang saat
 * muat ulang, jadi muat ulang langsung menampilkan maskot yang sudah duduk.
 */
let pending = false;

export function markMascotEntrance(): void {
  pending = true;
}

export function consumeMascotEntrance(): boolean {
  const value = pending;
  pending = false;
  return value;
}
