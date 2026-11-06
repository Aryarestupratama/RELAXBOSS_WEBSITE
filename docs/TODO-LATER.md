# RelaxBoss - TODO-LATER
Last updated: 2026-11-06

Ide dan fitur yang sengaja ditunda. Tidak dikerjakan sebelum masuk PRD dan Task Board (RULE-003). Fitur klien mobile dicatat di dokumen mobile; di sini hanya yang menyentuh backend web.

## Fitur dan produk

| Ide | Alasan ditunda | Catatan saat dikerjakan |
|---|---|---|
| Sisa API mobile: chat (TASK-M015) dan hapus akun (TASK-M016) | Dikerjakan di jalur mobile; ringkasan, consent, dan auth sudah ada | Controller API tipis di atas Service yang sama; token perangkat untuk notifikasi, platform, dan info perangkat lewat migration baru; aturan rinci di Architecture-M |
| Login Google | Disederhanakan | Paket Socialite; `google_id` unik, `password` boleh kosong; jangan simpan token OAuth |
| Ubah email akun | Mengubah email butuh verifikasi ulang; Akun hanya mengubah nama, jurusan, kampus | Alur verifikasi email baru sebelum email lama diganti |
| Hapus akun untuk pengguna yang belum verifikasi | Halaman Akun hanya terbuka setelah verifikasi email | Putuskan lewat route khusus atau tautan di halaman verifikasi |
| Autentikasi dua langkah untuk admin | Admin sudah bisa lebih dari satu (FR-035) dan memegang akses isi tanpa identitas | Paket tambahan dan layar baru |
| Peran admin terpisah (peninjau) | Semua admin saat ini punya hak sama | Satu migration bila peran baru dibutuhkan |
| Deteksi anomali intent versus mood | Chat dibuat tanpa kolom mood | Hitung di kode dengan membaca Mood Entry hari itu; tandai untuk tinjauan admin |
| Statistik per kampus atau jurusan | Kampus dan jurusan belum dipakai untuk statistik | Hanya agregat, kelompok < 5 disamarkan |
| Gender dan tanggal lahir | Tidak ada fitur yang memakainya | Tambahkan hanya bila ada tujuan jelas |
| Ringkasan percakapan dan judul otomatis oleh AI | Memakai kuota AI dan menambah data sensitif | Bila dipakai, terenkripsi |
| Valence dan arousal disimpan | Dihitung dari mood dan tenaga | Simpan hanya bila grafik lama tidak boleh berubah saat pemetaan diubah |
| Mode gelap | Rilis pertama mode terang | Token gelap didefinisikan bersamaan |
| Ekspor data pribadi oleh pengguna | Could | |
| Rincian Mood Tracker satu hari (semua entri hari itu berjam) | Tampilan dihapus saat penyederhanaan; FR-012 sudah disesuaikan dengan kode (hanya grafik 7 dan 30 hari) | Bila dikembalikan, ubah FR-012 dan Design SCR-016 lebih dulu |
| Dua sampai tiga artikel terbaru di Dashboard | Kartu tautan sudah cukup untuk rilis pertama | Query `Article::published()` melalui `DashboardPresenter`, saring slug `demo-` di produksi, dan jangan tampilkan isi sensitif |
| Artikel dipilih berdasarkan mood atau hasil Asesmen | Memakai data sensitif untuk personalisasi | Telaah privasi dahulu (RULE-032, RULE-042); jangan kirim ke AI |
| Tag, banyak penulis, riwayat revisi, dan hitungan pembaca untuk artikel | Satu tabel `articles` cukup untuk rilis pertama | Tabel `tags` dan pivot, `authors`, `article_revisions`; hitungan pembaca perlu telaah privasi dulu |
| Notifikasi email, web push, halaman publik `/pembaruan` | Notifikasi rilis pertama hanya dalam aplikasi (ADR-019) | Perlu tabel pengiriman dan command terjadwal |

## SEO dan web app

| Ide | Alasan ditunda | Catatan saat dikerjakan |
|---|---|---|
| `og:image` dan Twitter Card untuk halaman Publik (terutama artikel) | Hanya peningkatan tampilan saat dibagikan, bukan syarat FR-021 | Satu gambar bawaan di layout dan opsi gambar per artikel (kolom baru lewat migration) |
| `BreadcrumbList` dan `image` pada JSON-LD artikel | Data terstruktur `Article` sudah ada dan sah | Tambah di `PublicArticlePresenter::jsonLd()`; `image` menunggu ada gambar artikel |
| Penanda "membuka tab baru" pada tautan keluar di isi artikel | Tautan keluar memakai `target="_blank"` tanpa keterangan bagi pembaca layar; axe tidak menandainya | Proses HTML hasil `ArticleRenderer` agar menambah teks tersembunyi, atau hapus `open_in_new_window` |
| Service worker dan halaman offline untuk web app | Manifest dan ikon sudah ada; Chrome tetap menawarkan pemasangan tanpa service worker | Putuskan strategi cache lebih dulu agar data sensitif (Percakapan, catatan mood) tidak pernah tersimpan di cache peramban |
| Pintasan app (`shortcuts` di manifest: Mood Tracker, RelaxMate) | Peningkatan kecil | Tambah di `public/manifest.webmanifest`; tujuan URL di balik login |
| `icon-512` dari logo sumber beresolusi lebih tinggi | Ikon 512 saat ini hasil pembesaran logo 192px | Buat ulang dari PNG/SVG sumber, termasuk versi maskable |

## Komponen UI

| Ide | Alasan ditunda | Catatan saat dikerjakan |
|---|---|---|
| Bell sebagai popover dengan Esc | Lonceng berupa tautan cukup untuk rilis pertama | Putuskan CSP dan uji Radix Popover (tidak menyuntik `<style>`) terlebih dahulu |
| Menu aksi baris (⋯) di tabel admin memakai Radix DropdownMenu | Tombol aksi berpil di baris tabel cukup | Uji CSP lebih dulu seperti catatan Bell/Popover: pastikan Radix tidak menyuntik `<style>` tanpa nonce |
| Pencarian dengan debounce untuk daftar admin | Pencarian saat ini dikirim lewat tombol Cari | Debounce di `TableSearch`; filter dan paginasi tetap di server, jangan memuat semua data ke klien |
| Bersihkan alias `--soft-sky`, `--soft-mint`, dan `--soft-peach` yang menunjuk ke `--soft-lavender`, beserta prop `tone` | Alias lama masih dipakai banyak halaman | Audit pemakai dan kontras sebelum mengubah token; jangan ubah warna diam-diam |
| Hapus sisa kode mati: `Components/shared/DisclaimerNote.tsx`, Blade `x-help-box`, `x-disclaimer`, `x-info-tip`, `x-emotion-illustration` | Tidak dipakai lagi sejak `InlineNote` dan penyederhanaan Beranda (RULE-027) | Hapus lewat `git rm` di task pembersihan berikutnya |
