# RelaxBoss - Rules (Aturan Proyek)
Version: 1.6 | Status: Draft | Last updated: 2026-11-06
Depends on: PRD v1.9, Architecture v1.75, Schema v1.15, Design v1.76 | Owner: Founder (solo developer)

Dokumen ini adalah panduan kerja founder sebagai pengembang tunggal; bantuan AI boleh dipakai dengan tetap mengikuti seluruh aturan, melakukan verifikasi manual, dan tidak menganggap keluaran otomatis sebagai bukti kebenaran. Bila ada kontributor lain, aturan ini berlaku sama. Kata MUST, MUST NOT, SHOULD dipakai secara tegas. Kedalaman: **Lite**. Prinsip: sederhana, fungsi dulu.

---

## 1. Proses

| ID | Aturan |
|---|---|
| RULE-001 | Sebelum mulai bekerja, baca `progress.md`. Mulai dari task dengan status `Next`. |
| RULE-002 | Kerjakan satu task pada satu waktu. Kutip dengan ID (misalnya `TASK-012`). |
| RULE-003 | Dilarang menambah fitur di luar PRD. Ide baru ditulis di `TODO-LATER.md` (di folder dokumen, bukan di repo). |
| RULE-005 | **Doc Sync:** setiap perubahan perilaku, route, skema, atau keputusan memperbarui dokumen terkait dan `progress.md` pada akhir task yang sama (sebelum task ditandai `Done`). Dokumen disimpan di luar repo (RULE-014), jadi tidak ikut commit kode. |
| RULE-006 | Bila dokumen ambigu atau bertentangan, catat sebagai Open Question di `progress.md` dan putuskan sebelum melanjutkan; jangan menebak lalu menulisnya seolah fakta. Pengecualian: RULE-007 dan RULE-008. |
| RULE-007 | **Desain:** palet warna dan token sudah final. Tata letak belum final, jadi bangun versi sederhana yang mengutamakan fungsi memakai token dan komponen di `Design.md`. Jangan menunggu desain final dan jangan berhenti untuk memutuskan konsep halaman. |
| RULE-008 | **Data:** sebelum task hosting (TASK-031), jangan memakai data nyata dan jangan mengurus data nyata lebih awal. Pakai data dummy yang bertanda jelas (Instrumen berslug `demo-`, akun `example.test`, kontak krisis `dummy`). Data nyata diurus di TASK-030. |

---

## 2. Git

| ID | Aturan |
|---|---|
| RULE-010 | Satu branch saja: `main`. Kerja langsung di `main`. |
| RULE-011 | Alur setiap update: commit, lalu push. Tanpa branch, pull request, CI, atau hook. |
| RULE-012 | Pesan commit MUST selalu dalam bahasa Inggris, singkat dan jelas (misalnya `add login page`). |
| RULE-013 | `.env`, kunci, cadangan database, berkas ekspor data AI, dan file SQL tidak boleh masuk repo. |
| RULE-014 | **Dokumen proyek di luar repo.** PRD, Architecture, Schema, Design, Rules, `progress.md`, dan `TODO-LATER.md` (juga dokumen proyek mobile) disimpan di folder dokumen terpisah dari folder proyek dan tidak di-commit. Yang ada di repo hanya `README.md` ringkas (cara menjalankan dan gambaran proyek), contoh konfigurasi (`.env.example`, `.env.production.example`), dan aset kode seperti `resources/crisis/cases.json`. |

---

## 3. Kode

| ID | Aturan |
|---|---|
| RULE-020 | PHP mengikuti gaya Laravel; tipe parameter dan return eksplisit; `declare(strict_types=1);` pada kelas baru. |
| RULE-021 | TypeScript `strict: true`; hindari `any` dan `@ts-ignore`. |
| RULE-022 | Controller tipis: validasi lewat FormRequest, logika di Action atau Service. Tanpa query kompleks atau aturan bisnis di controller dan komponen React. |
| RULE-023 | Semua input pengguna divalidasi di server, meskipun sudah divalidasi di klien. |
| RULE-024 | Komponen React: fungsi dengan props bertipe, satu komponen per file, `PascalCase`; halaman di `Pages/`, komponen bersama di `Components/shared`. |
| RULE-025 | Tabel jamak snake_case, kelas `PascalCase`, variabel `camelCase`, konstanta `UPPER_SNAKE`. Identifier berbahasa Inggris, teks untuk pengguna Bahasa Indonesia. |
| RULE-026 | Konstanta produk (batas pesan, batas mood, versi consent, kontak bantuan) hanya dari `config/relaxboss.php`. |
| RULE-027 | Tidak ada kode mati, `console.log`, atau `dd()` yang tertinggal saat push. |

---

## 4. Arsitektur dan Data

| ID | Aturan |
|---|---|
| RULE-030 | Pemanggilan Groq hanya lewat `ChatCompletionClient` (yang memakai `GroqKeyManager`); dilarang memanggil `Http` ke Groq dari tempat lain. |
| RULE-031 | Migration ditulis **per task fitur**, bukan sekaligus. Satu perubahan per file, wajib ada `down()`. Migration yang sudah dijalankan di produksi tidak diubah. |
| RULE-032 | Isi sensitif (Percakapan, catatan mood, jawaban Asesmen, jawaban PFA, rekomendasi dan ringkasan AI) wajib memakai cast `encrypted` sesuai `Schema.md`. |
| RULE-033 | Query data pengguna wajib lewat relasi pemilik (`$user->moodEntries()` dan sejenisnya; tidak ada kelas Policy); dilarang `Model::find($id)` pada data pribadi tanpa pemeriksaan pemilik. |
| RULE-034 | Tidak ada queue worker, WebSocket, atau proses permanen. Pekerjaan terjadwal hanya lewat `schedule:run`. |
| RULE-035 | Waktu disimpan UTC; tanggal Mood Entry dihitung WIB hanya lewat `Support/Wib.php`. |
| RULE-036 | **Prompt AI milik founder.** Isi `resources/prompts/*.system.md` ditulis founder dari prompt yang sudah dimilikinya. Kode hanya menyediakan `PromptLoader`, konfigurasi `ai.features`, dan berkas placeholder bertanda `[PLACEHOLDER]` sampai prompt asli dimasukkan. Tiap fitur AI punya prompt sendiri. Teks pengguna (pesan chat, jawaban PFA) diperlakukan sebagai data, bukan instruksi. |

---

## 5. Keamanan dan Privasi

| ID | Aturan |
|---|---|
| RULE-040 | Dilarang mencatat di log: isi pesan, catatan mood, jawaban Asesmen, jawaban PFA, rekomendasi AI, email, sandi, API key (termasuk pratinjaunya). Log hanya kode, ID, fitur, nomor urut key, model, dan durasi. Token API mobile mentah juga tidak boleh masuk log atau respons selain saat diterbitkan. |
| RULE-041 | Dilarang mengirim nama, email, ID pengguna, atau kampus ke penyedia AI. Hanya isi percakapan (atau hasil Asesmen) dan jurusan (bila diisi) yang boleh dikirim. |
| RULE-042 | **Akses admin:** boleh membaca (hanya baca) isi percakapan dan rekomendasi AI Asesmen, **tanpa identitas** pemilik: tanpa nama, email, ID, jurusan, kampus; ID semu; hanya tanggal (tanpa jam); query tanpa relasi ke `users`; setiap pembukaan dicatat di `admin_access_logs`. Admin dilarang melihat catatan mood, jawaban Asesmen per butir, dan jawaban PFA. Agregat untuk kelompok < 5 disamarkan. |
| RULE-043 | Secret hanya di `.env`; dilarang di kode, commit, tangkapan layar, atau pesan error. |
| RULE-044 | Rate limit wajib pada login, registrasi, kirim ulang verifikasi, chat, dan entri mood, serta pada rute API mobile (tamu per IP, bertoken per pengguna). |
| RULE-045 | **Rotasi API key** (keputusan founder, ADR-009): boleh beberapa akun Groq milik founder, pool per fitur. Aturan: cooldown per model; bila semua key terbatas, jangan memaksa key atau mereset cooldown, beralih ke model cadangan lalu respons statis; 401 atau 403 menonaktifkan key sementara dan dihitung sebagai galat; tidak ada key atau pratinjaunya di log. |
| RULE-046 | Hasil Asesmen selalu menyertakan disclaimer "bukan diagnosis" dan jalur ke profesional. |
| RULE-047 | Setelah mengubah prompt AI mana pun atau `resources/crisis/keywords.id.php`, **periksa manual** `resources/crisis/cases.json` (≥ 30 kasus) sebelum push: semua kasus positif harus memicu Crisis Response, baik di chat maupun pada jawaban PFA. |
| RULE-047A | Jawaban PFA MUST diperiksa `CrisisDetector` sebelum dikirim ke AI. Bila terdeteksi krisis, jawaban tidak dikirim ke AI, banner dan kontak bantuan tampil, dan rekomendasi memakai teks statis. |
| RULE-048 | **Pengaman produksi:** aplikasi tidak boleh go-live bila `relaxboss:preflight` gagal (kontak krisis kosong atau dummy, Instrumen `demo-` aktif, akun dummy, prompt masih `[PLACEHOLDER]`, `APP_DEBUG` aktif, `MAIL_MAILER=log`). Klaim UMB yang belum dikonfirmasi tidak ditampilkan. |
| RULE-049 | Klaim publik (kolaborasi UMB, validasi psikolog, jumlah pengguna, enkripsi, ulasan) hanya boleh memuat yang sudah terkonfirmasi dan benar. Teks consent dan Privasi wajib menyebut tinjauan admin tanpa identitas dan persetujuan pelatihan. |

---

## 6. UI

| ID | Aturan |
|---|---|
| RULE-050 | Komponen memakai shadcn dan token di `app.css`; dilarang nilai heksadesimal, `rgb()/hsl()`, palet bawaan Tailwind (mis. `red-500`), `bg-white` atau `bg-black`, dan kelas `.rb-*` langsung di komponen atau view. Warna transparan atau bayangan diturunkan dari token (`color-mix` atau pengubah opasitas), bukan angka terpisah. |
| RULE-051 | Satu library ikon (Lucide). Tidak menambah library ikon, chart, atau animasi tanpa catatan di `Architecture.md` bagian 13. |
| RULE-052 | Teks pada permukaan pastel MUST memakai `--text` navy; jangan mengandalkan warna saja untuk status atau makna. Pengecualian hanya jika kontras terukur dan dicatat di Design. |
| RULE-053 | Setiap layar punya status memuat, kosong, galat, dan sukses (`Design.md` bagian 5). |
| RULE-054 | Teks UI diambil dari bank teks `Design.md` bila ada; teks baru mengikuti nada yang sama. |
| RULE-055 | Label, fokus keyboard, dan kontras diperiksa saat mengerjakan UI. |

---

## 7. Dependency

| ID | Aturan |
|---|---|
| RULE-060 | Menambah paket cukup dicatat dengan alasan satu kalimat di `Architecture.md` bagian 13. Paket di daftar "Tidak dibawa" (termasuk Socialite dan paket test) tidak ditambahkan tanpa alasan tercatat. Sanctum sudah dipasang dengan alasan tercatat di Architecture bagian 13 (ADR-M02). |

---

## 8. Ekspor dan Pelatihan Data

| ID | Aturan |
|---|---|
| RULE-070 | Ekspor data AI hanya lewat perintah Artisan `relaxboss:export-ai-data` (tanpa route web, tanpa jadwal), hanya pengguna dengan `ai_training_consent_choice = granted`, tanpa nama, email, ID, jurusan, kampus; ID record acak per ekspor; kontak krisis diganti penanda. Berkas ekspor non-publik, tidak masuk repo, dihapus setelah dipakai. |
| RULE-071 | Persetujuan pelatihan terpisah dari consent AI: tanpa nilai awal (pengguna wajib memilih), penolakan tidak mengurangi akses ke fitur, dapat diubah kapan saja, dan teks menyebut bahwa percakapan krisis ikut diekspor dan data yang sudah diekspor tidak bisa ditarik. |
| RULE-072 | Fitur AI baru MUST mendaftarkan eksportirnya lewat kontrak `AiDataExporter` dan `config/relaxboss.php`, bukan perintah ekspor baru. |
| RULE-073 | Data hasil ekspor tidak dipakai melatih model sebelum ditinjau manual dan sebelum OQ-5 (telaah hukum) dijawab. |

---

## 9. API Mobile

| ID | Aturan |
|---|---|
| RULE-080 | API mobile (`/api/v1`) hanya lapisan tipis di atas Action dan Service yang sama dengan web: controller di `Http/Controllers/Api/V1`, validasi lewat FormRequest, respons lewat API Resource, galat seragam `{ "error": { "code", "message" } }`. Aturan rinci klien dan API mobile ada di dokumen mobile (Architecture-M, Rules-M). |
| RULE-081 | Aksi tulis API butuh token Sanctum dan email terverifikasi (`api.verified`). Teks consent, batas, dan kontak bantuan dibaca dari `config/relaxboss.php` lewat `ConsentInfo` dan `CrisisContactList`, tidak digandakan di klien maupun kode. CORS tetap mati (klien hanya aplikasi native). |

---

## 10. Definition of Done

Sebuah task selesai bila:
1. Perilaku sesuai FR yang dirujuk dan sudah dicoba manual di lokal (alur utama dan kasus gagal yang disebut di Task Board).
2. Tidak ada data sensitif di log (RULE-040) dan `npm run build` berhasil (bila task menyentuh JS atau CSS).
3. Dokumen terkait dan `progress.md` sudah diperbarui (RULE-005).
4. Sudah di-commit dan di-push ke `main`.

Tidak ada syarat test, CI, atau staging.

---

## 11. Daftar Larangan Cepat

Jangan: menambah fitur di luar PRD, menggandakan teks consent atau kontak bantuan di klien, memanggil Groq langsung, mencatat isi pesan atau key di log, mengirim identitas atau kampus ke AI, membuka identitas atau data pribadi untuk admin, mengekspor data tanpa persetujuan, memakai warna langsung di komponen, mengubah migration yang sudah di produksi, menunggu desain final atau memakai data nyata sebelum waktunya, memasukkan dokumen proyek ke repo, menebak saat dokumen ambigu.

---

## Assumptions
- A-1: Aturan ini berlaku untuk founder sebagai pengembang tunggal; bila ada kontributor lain, berlaku sama.
- A-2: Pint dan ESLint boleh dijalankan manual di lokal, tetapi bukan gerbang.

## Change Log

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.6 | 2026-11-06 | Doc Sync: RULE-014 (contoh `.env`), RULE-033 (tanpa Policy), RULE-040/044 (token dan rute API), RULE-060 (Sanctum dipasang), bagian 9 API Mobile (RULE-080, RULE-081). |
| 1.5 | 2026-10-27 | RULE-052 diringkas: teks navy `--text` di atas pastel; pembaruan status dokumen dan alur Doc Sync. |
| 1.4 | 2026-10-26 | Audit kontras dan larangan warna langsung diperluas. |
| 1.0–1.3 | 2026-10-04–10 | Aturan dasar workflow, keamanan, data dummy, consent, dan Doc Sync. |
