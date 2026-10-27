# RelaxBoss

Aplikasi web pendamping kesehatan mental untuk mahasiswa: Asesmen mandiri, Mood Tracker, dan RelaxMate (teman curhat berbasis AI). RelaxBoss **bukan layanan medis** dan tidak menggantikan psikolog atau psikiater; hasil Asesmen bukan diagnosis.

Dibangun dan dikelola oleh satu orang (founder). Dokumen proyek (PRD, Architecture, Schema, Design, Rules, progress) disimpan di luar repo ini.

## Fitur

- **Asesmen**: kuesioner dengan skor per subskala, langkah PFA, riwayat, dan Rekomendasi AI (butuh persetujuan).
- **Mood Tracker**: catatan mood dan tenaga, grafik 7 dan 30 hari.
- **RelaxMate**: percakapan dengan AI, pemeriksaan krisis berbasis aturan sebelum AI dipanggil, dan kontak bantuan.
- **Admin**: statistik agregat, kelola Instrumen, kelola akun, dan tinjauan percakapan tanpa identitas (tercatat, hanya baca).
- **Halaman Publik**: Beranda, Tentang, Asesmen, Konsultasi, Privasi, Ketentuan, dengan sitemap dan robots dinamis.

## Stack

Laravel 13, PHP 8.3, Inertia 3, React 19, TypeScript (strict), Tailwind 4, shadcn/ui. Database MySQL di produksi dan SQLite di lokal. AI lewat Groq. Tanpa queue worker; pekerjaan terjadwal hanya lewat `schedule:run`.

## Menjalankan di lokal

Syarat: PHP 8.3 atau lebih baru, Composer, Node.js, dan npm.

```bash
composer install
cp .env.example .env          # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build                 # atau: npm run dev (Vite) bersama php artisan serve
php artisan serve
```

Seeder lokal membuat data **dummy** saja: akun `demo-a@example.test`, `demo-b@example.test`, dan seterusnya (sandi `password`), serta Instrumen berslug `demo-`. Admin dibuat dari `ADMIN_SEED_EMAIL` dan `ADMIN_SEED_PASSWORD` di `.env`. Seeder demo tidak berjalan di luar lingkungan `local`.

Untuk menguji AI sungguhan, isi `GROQ_KEYS_CHAT` dan `GROQ_KEYS_ASSESSMENT` di `.env`. Tanpa key, fitur AI memakai teks statis.

## Perintah Artisan penting

| Perintah | Fungsi |
|---|---|
| `php artisan relaxboss:check-crisis` | Periksa `resources/crisis/cases.json`; jalankan setiap prompt AI atau kata kunci krisis berubah |
| `php artisan relaxboss:preflight` | Periksa kesiapan produksi; gagal berarti jangan go-live |
| `php artisan relaxboss:backup-db` | Cadangkan database ke `storage/app/private/backups` (dijadwalkan harian) |
| `php artisan relaxboss:purge-content` | Hapus Instrumen, hasil Asesmen, artikel, chat, mood, dan log sebelum go-live (akun tetap); jalankan `relaxboss:backup-db` dulu |
| `php artisan relaxboss:export-ai-data {chat\|assessment}` | Ekspor data AI dari pengguna yang menyetujui, tanpa identitas |

Cron produksi: `* * * * * php artisan schedule:run`. Contoh konfigurasi produksi ada di `.env.production.example`.

## Struktur singkat

```
app/Actions, app/Services     logika bisnis (controller tipis)
app/Http                      controller, request, middleware
config/relaxboss.php          semua konstanta produk
resources/js                  halaman dan komponen React (Inertia)
resources/views               halaman Publik (Blade) dan email
resources/prompts             prompt sistem AI, satu per fitur
resources/crisis              kata kunci dan kasus uji krisis
```

## Privasi dan keamanan

- Isi sensitif (pesan, catatan mood, jawaban Asesmen, rekomendasi AI) disimpan terenkripsi.
- Nama, email, ID, dan kampus tidak dikirim ke penyedia AI.
- Admin hanya dapat membaca isi percakapan dan rekomendasi AI tanpa identitas, dan setiap pembukaan dicatat.
- Rahasia hanya di `.env`; jangan pernah memasukkan `.env`, key, atau cadangan database ke repo.

Detail lengkap ada di halaman Privasi dan Ketentuan di aplikasi.
