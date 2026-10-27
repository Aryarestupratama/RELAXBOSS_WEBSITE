<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * Konten artikel edukasi asli RelaxBoss.
 *
 * Jalankan secara eksplisit saat deploy:
 * php artisan db:seed --class=ArticleContentSeeder --force
 *
 * Seeder idempotent: slug menjadi kunci updateOrCreate, sehingga dapat dijalankan
 * ulang untuk memperbarui konten tanpa membuat duplikat. Seeder ini sengaja tidak
 * dipanggil otomatis dari DatabaseSeeder agar konten produksi bisa dirilis dengan sadar.
 */
class ArticleContentSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'slug' => 'memahami-stres-akademik-dan-mulai-mengelolanya',
                'title' => 'Memahami Stres Akademik dan Mulai Mengelolanya',
                'excerpt' => 'Kenali pemicu stres kuliah dan susun langkah kecil yang realistis tanpa menuntut diri selalu produktif.',
                'meta_title' => 'Stres Akademik: Cara Mengenali dan Mengelolanya',
                'meta_description' => 'Pelajari pemicu stres akademik, cara memecah tugas menjadi langkah kecil, dan kapan perlu mencari dukungan.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Stres saat kuliah bukan berarti kamu gagal

Tugas yang menumpuk, jadwal ujian, masalah biaya, kerja paruh waktu, atau harapan keluarga bisa membuat masa kuliah terasa berat. Stres adalah respons terhadap tuntutan yang dirasakan melebihi sumber daya yang tersedia. Respons ini tidak otomatis berarti seseorang mengalami gangguan mental; konteks, durasi, dan dampaknya pada kehidupan sehari-hari juga penting.

Stres sesekali dapat menjadi sinyal bahwa ada sesuatu yang perlu diatur ulang. Namun, stres yang terus-menerus dan mengganggu tidur, konsentrasi, hubungan, atau kegiatan harian layak mendapat perhatian.

## Mulai dari hal yang bisa dikendalikan

1. **Keluarkan semua tugas dari kepala.** Tulis tugas, tenggat, dan langkah berikutnya di satu tempat.
2. **Pilih satu langkah kecil.** Alih-alih menulis “selesaikan makalah”, pilih “buka panduan tugas dan tulis tiga poin”.
3. **Bedakan penting dan mendesak.** Tentukan satu atau dua prioritas realistis untuk hari ini.
4. **Jadwalkan jeda.** Istirahat singkat, makan, minum, bergerak, dan tidur adalah kebutuhan, bukan hadiah setelah semua tugas selesai.
5. **Bicarakan kendala.** Jika beban tidak realistis, hubungi dosen, pembimbing akademik, teman, atau keluarga yang dapat membantu mencari pilihan.

Panduan WHO tentang pengelolaan stres juga menekankan latihan sederhana seperti kembali menyadari keadaan sekitar, memberi jarak dari pikiran yang menyulitkan, dan mengambil tindakan kecil sesuai nilai yang penting bagi diri sendiri.

## Kapan perlu mencari bantuan?

Jika stres terasa sulit dikendalikan, berlangsung lama, atau membuatmu kesulitan menjalani kegiatan sehari-hari, pertimbangkan berbicara dengan tenaga kesehatan mental atau layanan konseling yang tersedia. Kamu tidak harus menunggu sampai keadaan menjadi sangat berat untuk meminta dukungan.

**Catatan:** Artikel ini untuk edukasi, bukan diagnosis atau pengganti konsultasi profesional.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'teknik-grounding-saat-pikiran-terasa-penuh',
                'title' => 'Teknik Grounding saat Pikiran Terasa Penuh',
                'excerpt' => 'Latihan grounding membantu mengarahkan perhatian kembali ke momen sekarang ketika pikiran terasa ramai atau kewalahan.',
                'meta_title' => 'Teknik Grounding Sederhana untuk Mengelola Stres',
                'meta_description' => 'Coba latihan grounding yang lembut untuk mengarahkan perhatian ke keadaan saat ini ketika merasa kewalahan.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Mengapa perhatian perlu dikembalikan ke saat ini?

Ketika tertekan, pikiran bisa berputar pada hal yang sudah terjadi atau pada kemungkinan buruk di masa depan. Grounding adalah latihan untuk mengarahkan perhatian kembali pada pengalaman saat ini—misalnya sensasi kaki menyentuh lantai, suara di sekitar, atau benda yang terlihat.

Grounding bukan tombol yang menghapus masalah dan tidak selalu membuat perasaan langsung hilang. Tujuannya adalah memberi perhatian sebuah jangkar agar kamu dapat memilih langkah berikutnya dengan lebih sadar.

## Coba latihan singkat ini

- Letakkan kedua kaki di lantai dan rasakan titik sentuhnya.
- Sebutkan dalam hati lima benda yang kamu lihat.
- Perhatikan tiga suara yang terdengar, tanpa harus menilai suara itu.
- Rasakan satu sensasi tubuh yang netral, seperti telapak tangan menyentuh kain.
- Sebutkan tempat kamu berada dan satu hal kecil yang akan kamu lakukan berikutnya.

Lakukan dengan mata terbuka jika itu terasa lebih nyaman. Kamu boleh melewatkan langkah yang membuat tidak nyaman dan kembali pada cara sederhana seperti menyebutkan benda di ruangan.

## Jangan jadikan latihan sebagai ujian

Tidak ada nilai untuk “berhasil tenang”. Jika pikiran kembali mengembara, cukup sadari dan arahkan perhatian lagi. Sebagian orang menyukai grounding; sebagian lain lebih terbantu dengan berjalan sebentar, berbicara dengan orang tepercaya, atau mendapat bantuan profesional.

WHO memasukkan grounding sebagai salah satu keterampilan dalam panduan pengelolaan stres. Adaptasikan latihan sesuai kebutuhanmu, bukan sebagai pengganti pertolongan ketika kamu merasa tidak aman atau kesulitan menjalani aktivitas.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- WHO Thailand, [Doing What Matters in Times of Stress](https://www.who.int/thailand/news/feature-stories/detail/doing-what-matters-in-times-of-stress)
MARKDOWN,
            ],
            [
                'slug' => 'membuat-rencana-harian-yang-lebih-manusiawi',
                'title' => 'Membuat Rencana Harian yang Lebih Manusiawi',
                'excerpt' => 'Susun rencana yang mempertimbangkan energi, jeda, dan hal tak terduga—bukan hanya daftar pekerjaan.',
                'meta_title' => 'Cara Membuat Rencana Harian yang Realistis',
                'meta_description' => 'Atur prioritas dan jadwal dengan realistis, termasuk waktu istirahat dan ruang untuk hal tak terduga.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Jadwal yang penuh bukan selalu jadwal yang baik

Daftar tugas yang panjang dapat memberi kesan bahwa semua harus selesai sekaligus. Padahal, energi, waktu, dan fokus manusia terbatas. Rencana yang bermanfaat bukan jadwal yang sempurna, melainkan panduan yang bisa disesuaikan ketika keadaan berubah.

## Metode sederhana untuk merencanakan hari

1. **Pilih maksimal tiga prioritas utama.** Sisanya boleh masuk daftar tambahan, bukan dianggap wajib selesai.
2. **Ubah tujuan besar menjadi tindakan konkret.** “Belajar statistik” bisa diubah menjadi “mengerjakan lima soal latihan”.
3. **Perkirakan waktu secara jujur.** Sisakan ruang di antara kegiatan untuk perjalanan, makan, dan perubahan rencana.
4. **Masukkan kebutuhan dasar.** Tidur, makan, bergerak, dan waktu bersosialisasi perlu mendapat tempat.
5. **Tutup hari dengan evaluasi singkat.** Tanyakan apa yang berhasil, apa yang menghambat, dan apa satu langkah yang perlu dipindahkan.

Jika kamu sering melewatkan jadwal, itu tidak selalu berarti kurang disiplin. Mungkin tugas terlalu besar, perkiraan waktunya tidak realistis, atau kamu sedang menghadapi kendala yang membutuhkan dukungan.

## Berlatih mengatakan “belum sekarang”

Saat ada permintaan tambahan, kamu dapat meminta waktu untuk memeriksa jadwal sebelum menyanggupi. Menunda atau menolak tugas tambahan dengan sopan bisa membantu menjaga komitmen yang sudah ada.

NIMH menyarankan untuk menetapkan tujuan dan prioritas, menentukan apa yang harus dilakukan sekarang dan apa yang dapat menunggu, serta belajar mengatakan tidak ketika beban mulai berlebihan.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
MARKDOWN,
            ],
            [
                'slug' => 'berbicara-kepada-diri-sendiri-tanpa-menghakimi',
                'title' => 'Berbicara kepada Diri Sendiri tanpa Menghakimi',
                'excerpt' => 'Belajar mengenali kritik diri dan menggantinya dengan bahasa yang lebih akurat serta suportif.',
                'meta_title' => 'Mengurangi Kritik Diri dengan Self-Compassion',
                'meta_description' => 'Kenali kritik diri dan coba berbicara kepada diri dengan lebih adil tanpa mengabaikan tanggung jawab.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Mengoreksi diri tidak harus menyakiti diri

Setelah nilai kurang baik atau rencana gagal, sebagian orang langsung menyimpulkan bahwa dirinya tidak mampu. Namun, satu hasil tidak menggambarkan seluruh kemampuan atau nilai diri. Kritik yang terlalu keras juga belum tentu membantu kita memahami apa yang perlu diperbaiki.

Sikap welas asih terhadap diri sendiri bukan berarti membenarkan semua tindakan atau berhenti bertanggung jawab. Artinya, mengakui kesulitan secara jujur sambil tetap memperlakukan diri sebagai manusia yang layak dihormati.

## Coba ubah cara merumuskan pikiran

- Dari “Aku selalu gagal” menjadi “Kali ini hasilnya belum sesuai harapan.”
- Dari “Aku harus bisa semuanya” menjadi “Aku perlu memilih prioritas dan meminta bantuan bila perlu.”
- Dari “Aku tidak boleh istirahat” menjadi “Istirahat membantu menjaga kemampuan untuk melanjutkan.”
- Dari “Orang lain pasti menilaiku” menjadi “Aku belum tahu apa yang dipikirkan orang lain.”

Kamu tidak perlu memaksa diri berpikir positif. Cukup cari kalimat yang lebih seimbang, sesuai fakta, dan memberi ruang untuk tindakan berikutnya.

## Latihan dua kolom

Tulis kalimat keras yang muncul, lalu tulis respons yang akan kamu berikan kepada teman dalam situasi serupa. Perhatikan apakah respons kepada diri sendiri jauh lebih kejam daripada respons kepada orang lain. Pilih satu kalimat yang terasa masuk akal untuk digunakan hari ini.

Jika rasa bersalah, malu, atau kritik diri terasa menetap dan mengganggu keseharian, berbicara dengan konselor atau tenaga kesehatan mental bisa membantu mengeksplorasi pola tersebut.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'menghadapi-tumpukan-tugas-dengan-langkah-kecil',
                'title' => 'Menghadapi Tumpukan Tugas dengan Langkah Kecil',
                'excerpt' => 'Saat tugas terasa terlalu besar, pecah pekerjaan menjadi tindakan kecil yang bisa dimulai sekarang.',
                'meta_title' => 'Cara Menghadapi Tumpukan Tugas Kuliah',
                'meta_description' => 'Strategi praktis memecah tugas kuliah, memilih prioritas, dan meminta klarifikasi sebelum kewalahan.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Mulai sering kali lebih sulit daripada melanjutkan

Tugas besar tidak selalu sulit karena materinya saja. Ketidakjelasan langkah pertama, takut hasil tidak sempurna, atau banyaknya tenggat dapat membuat kita menunda. Menyebut diri malas biasanya tidak menjelaskan masalah yang sebenarnya.

## Pecah tugas sampai langkahnya jelas

Misalnya, “menyelesaikan presentasi” dapat dipecah menjadi:
1. Membaca instruksi dan rubrik.
2. Menentukan pertanyaan utama.
3. Mengumpulkan tiga sumber yang relevan.
4. Membuat kerangka lima bagian.
5. Mengisi satu bagian terlebih dahulu.
6. Memeriksa sitasi dan latihan presentasi.

Pilih langkah yang cukup kecil untuk dikerjakan dalam satu sesi singkat. Setelahnya, berhenti sejenak dan putuskan langkah selanjutnya. Jika instruksi tidak jelas, mengirim pertanyaan kepada dosen atau anggota kelompok dapat lebih berguna daripada menebak.

## Hindari menjadikan teknik produktivitas sebagai tekanan baru

Timer, daftar tugas, atau metode belajar tertentu adalah alat, bukan aturan mutlak. Bila satu cara tidak cocok, sesuaikan. Sisakan waktu untuk makan, tidur, dan pemulihan. Bila beban kerja tidak mungkin selesai dalam waktu yang tersedia, komunikasikan lebih awal dan diskusikan pilihan yang realistis.

Jika penundaan terjadi bersama kecemasan berat, kesulitan konsentrasi yang menetap, atau gangguan fungsi sehari-hari, pertimbangkan mencari dukungan profesional atau layanan kampus.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
MARKDOWN,
            ],
            [
                'slug' => 'mengelola-perubahan-rencana-dan-ketidakpastian',
                'title' => 'Mengelola Perubahan Rencana dan Ketidakpastian',
                'excerpt' => 'Saat keadaan tidak berjalan sesuai rencana, fokus pada hal yang bisa dipengaruhi dan beri ruang untuk penyesuaian.',
                'meta_title' => 'Menghadapi Ketidakpastian dengan Lebih Terarah',
                'meta_description' => 'Langkah sederhana menghadapi rencana yang berubah tanpa menuntut diri mengendalikan semua hal.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Tidak semua hal bisa dipastikan

Nilai, keputusan organisasi, kondisi keluarga, kesehatan, dan peluang kerja tidak selalu berada dalam kendali kita. Ketidakpastian dapat membuat pikiran berusaha mencari jawaban untuk semua kemungkinan. Namun, terus memikirkan hal yang belum dapat dipastikan bisa menghabiskan energi tanpa memberi keputusan baru.

## Pisahkan kendali, pengaruh, dan penerimaan

Ambil selembar kertas dan buat tiga bagian:
- **Bisa saya kendalikan:** langkah belajar, pertanyaan yang akan diajukan, atau waktu untuk menghubungi seseorang.
- **Bisa saya pengaruhi:** pembagian tugas kelompok atau waktu konsultasi dengan dosen.
- **Belum bisa saya ubah saat ini:** keputusan orang lain atau hasil yang belum diumumkan.

Pilih satu tindakan dari bagian pertama. Untuk bagian kedua, tentukan komunikasi yang masuk akal. Untuk bagian ketiga, akui bahwa jawabannya belum tersedia dan arahkan perhatian pada kegiatan yang tetap penting hari ini.

## Tetap fleksibel

Rencana adalah alat bantu, bukan kontrak yang harus dipertahankan meski keadaan berubah. Perbarui rencana saat ada informasi baru. Jika ketidakpastian terus memicu kecemasan yang mengganggu tidur, belajar, atau hubungan, pertimbangkan mencari dukungan.

WHO merekomendasikan keterampilan menghadapi stres yang dapat dilatih secara bertahap, termasuk mengarahkan perhatian pada saat ini dan bertindak sesuai hal yang dianggap penting.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'rutinitas-malam-untuk-tidur-lebih-teratur',
                'title' => 'Rutinitas Malam untuk Tidur Lebih Teratur',
                'excerpt' => 'Bangun rutinitas yang sederhana dan konsisten agar tubuh mendapat kesempatan beralih dari aktivitas ke istirahat.',
                'meta_title' => 'Rutinitas Malam agar Tidur Lebih Teratur',
                'meta_description' => 'Tips membangun rutinitas tidur, mengurangi gangguan sebelum tidur, dan mencari bantuan bila masalah menetap.',
                'category' => ArticleCategory::Sleep,
                'body_markdown' => <<<'MARKDOWN'
## Tidur dipengaruhi kebiasaan dan keadaan

Tidur yang cukup mendukung perhatian, suasana hati, dan fungsi tubuh. Namun, sulit tidur tidak selalu bisa diselesaikan hanya dengan “tidur lebih awal”. Jadwal kuliah, kerja, lingkungan kamar, stres, kondisi kesehatan, dan kebiasaan sehari-hari juga berperan.

## Buat rutinitas yang realistis

- Usahakan waktu bangun relatif konsisten, termasuk saat akhir pekan bila memungkinkan.
- Sisihkan waktu untuk beralih dari aktivitas ke istirahat: merapikan barang, mandi hangat, membaca, atau mendengarkan audio yang menenangkan.
- Redupkan cahaya dan kurangi penggunaan layar menjelang tidur jika layar membuatmu tetap terjaga.
- Hindari minuman berkafein pada sore atau malam hari jika kamu sensitif terhadap efeknya.
- Buat kamar senyaman yang memungkinkan—tidak semua orang punya kendali penuh atas kebisingan atau ruang tidur.

Bagi kebanyakan orang dewasa, rekomendasi umum adalah tidur setidaknya tujuh jam per malam, tetapi kebutuhan individu dapat berbeda. Jangan menganggap satu malam yang buruk sebagai kegagalan; kembali ke kebiasaan yang membantu pada malam berikutnya.

## Jika masalah tidur menetap

Catat pola tidur dan hal yang mungkin memengaruhinya, tanpa menjadikannya sumber tekanan baru. Jika kesulitan tidur berlangsung terus-menerus atau mengganggu kegiatan siang hari, konsultasikan dengan tenaga kesehatan. Gangguan tidur dapat memiliki berbagai penyebab dan kadang memerlukan penanganan khusus.

### Referensi
- CDC, [About Sleep](https://www.cdc.gov/sleep/about/index.html)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'mengapa-tidur-berpengaruh-pada-suasana-hati',
                'title' => 'Mengapa Tidur Berpengaruh pada Suasana Hati?',
                'excerpt' => 'Tidur dan kesejahteraan emosional saling berkaitan; pahami hubungan ini tanpa menyalahkan diri ketika sulit tidur.',
                'meta_title' => 'Hubungan Tidur dan Suasana Hati',
                'meta_description' => 'Pahami kaitan tidur dengan perhatian dan suasana hati serta langkah sederhana menjaga kebiasaan tidur.',
                'category' => ArticleCategory::Sleep,
                'body_markdown' => <<<'MARKDOWN'
## Tidur bukan sekadar waktu berhenti beraktivitas

Kualitas dan durasi tidur berkaitan dengan perhatian, daya ingat, energi, dan kesejahteraan emosional. Saat kurang tidur, tugas yang biasa terasa ringan dapat terasa lebih sulit dan emosi mungkin lebih sulit dikelola. Sebaliknya, stres dan kekhawatiran juga dapat mengganggu tidur. Hubungan ini bisa berjalan dua arah.

Ini bukan berarti semua perubahan suasana hati disebabkan oleh tidur. Faktor sosial, kesehatan fisik, tekanan akademik, dan kondisi mental juga dapat berperan.

## Apa yang bisa dicoba?

1. Perhatikan jam tidur dan bangun selama beberapa hari untuk melihat pola, bukan untuk menghakimi diri.
2. Pilih satu perubahan kecil, misalnya mengurangi kafein pada sore hari atau membuat rutinitas sebelum tidur.
3. Cari cahaya alami dan lakukan aktivitas fisik yang sesuai kemampuan pada siang hari.
4. Beri waktu untuk menyelesaikan kekhawatiran sebelum masuk tempat tidur—misalnya menulis tugas untuk besok.
5. Jika belum tertidur, hindari memaksa diri sambil terus melihat jam; pilih aktivitas tenang sebentar lalu kembali saat mulai mengantuk.

## Hindari menyalahkan diri

Mahasiswa yang tinggal bersama orang lain, bekerja sambil kuliah, atau memiliki tanggung jawab keluarga mungkin tidak selalu dapat mengatur jadwal tidur dengan leluasa. Mulailah dari hal yang realistis dalam keadaanmu.

Jika masalah tidur menetap, menyebabkan kantuk berat di siang hari, atau muncul bersama perubahan suasana hati yang mengganggu, bicarakan dengan tenaga kesehatan.

### Referensi
- CDC, [About Sleep](https://www.cdc.gov/sleep/about/index.html)
- CDC, [About Sleep and Your Heart Health](https://www.cdc.gov/heart-disease/about/sleep-and-heart-health.html)
MARKDOWN,
            ],
            [
                'slug' => 'kebiasaan-kecil-yang-mendukung-kualitas-tidur',
                'title' => 'Kebiasaan Kecil yang Mendukung Kualitas Tidur',
                'excerpt' => 'Perubahan kecil pada jadwal, lingkungan, dan kebiasaan sore hari bisa mendukung tidur yang lebih teratur.',
                'meta_title' => 'Kebiasaan Sehari-hari untuk Mendukung Tidur',
                'meta_description' => 'Kenali kebiasaan tidur yang dapat dicoba secara bertahap, dari jadwal hingga lingkungan kamar.',
                'category' => ArticleCategory::Sleep,
                'body_markdown' => <<<'MARKDOWN'
## Mulai dari satu kebiasaan, bukan semuanya sekaligus

Tips tidur di internet sering tampak seperti daftar aturan panjang. Jika semuanya dicoba sekaligus, rutinitas baru malah terasa membebani. Pilih satu kebiasaan yang paling mungkin dilakukan selama beberapa hari.

### Pilihan yang bisa dicoba

- **Jadwal:** pertahankan waktu bangun yang cukup konsisten sesuai kondisi.
- **Cahaya:** cari cahaya alami pada pagi atau siang hari.
- **Gerak:** lakukan aktivitas fisik yang sesuai kemampuan, tetapi perhatikan apakah olahraga dekat waktu tidur membuatmu lebih terjaga.
- **Kafein:** amati pengaruh kopi, teh, minuman energi, atau sumber kafein lain pada tidurmu.
- **Lingkungan:** kurangi cahaya dan kebisingan sebisa mungkin; gunakan penutup mata atau penyumbat telinga hanya jika aman dan nyaman.
- **Layar:** kurangi penggunaan perangkat menjelang tidur jika membuatmu terus aktif atau menunda waktu istirahat.

Kebutuhan tidur berbeda menurut usia dan individu. Untuk kebanyakan orang dewasa, panduan umum menyarankan setidaknya tujuh jam per malam. Angka ini bukan ukuran moral dan tidak berarti semua orang dapat mencapai jadwal yang sama setiap hari.

## Jika cara umum tidak cukup

Kesulitan tidur yang berkepanjangan dapat berkaitan dengan kondisi medis, obat, stres, atau gangguan tidur. Jangan menambah suplemen atau obat tidur tanpa berkonsultasi dengan tenaga kesehatan. Mintalah penilaian profesional jika masalah berulang atau mengganggu aktivitas.

### Referensi
- CDC, [About Sleep](https://www.cdc.gov/sleep/about/index.html)
- NHS, [Insomnia](https://www.nhs.uk/conditions/insomnia/)
MARKDOWN,
            ],
            [
                'slug' => 'menghadapi-sulit-tidur-karena-banyak-pikiran',
                'title' => 'Menghadapi Sulit Tidur karena Banyak Pikiran',
                'excerpt' => 'Saat pikiran terus aktif menjelang tidur, coba pisahkan waktu merencanakan hari dari waktu beristirahat.',
                'meta_title' => 'Sulit Tidur karena Banyak Pikiran: Apa yang Bisa Dicoba?',
                'meta_description' => 'Langkah lembut menghadapi pikiran yang ramai saat malam dan mengetahui kapan perlu konsultasi.',
                'category' => ArticleCategory::Sleep,
                'body_markdown' => <<<'MARKDOWN'
## Malam yang sunyi dapat membuat kekhawatiran terasa lebih keras

Ketika aktivitas berkurang, pikiran tentang tugas, relasi, atau masa depan bisa terasa semakin jelas. Memaksa diri untuk berhenti berpikir sering justru membuat frustrasi. Tujuannya bukan mengosongkan pikiran sepenuhnya, melainkan mengurangi perjuangan terhadap pikiran dan memberi ruang untuk beristirahat.

## Buat “waktu untuk memikirkan besok”

Sebelum rutinitas tidur, luangkan beberapa menit untuk:
1. Menulis hal yang sedang mengkhawatirkanmu.
2. Menandai apakah ada tindakan kecil yang bisa dilakukan besok.
3. Menuliskan kapan tindakan itu akan dimulai.
4. Menutup catatan dan mengingatkan diri bahwa kamu sudah menyediakan waktu untuk menanganinya.

Jika pikiran muncul lagi di tempat tidur, kamu bisa mengatakan, “Aku sudah mencatat ini; aku akan melihatnya besok.” Ini tidak menjamin tidur langsung datang, tetapi dapat membantu memindahkan rencana dari kepala ke catatan.

Cobalah aktivitas tenang seperti membaca ringan atau mendengarkan suara yang nyaman. Jika latihan napas terasa tidak nyaman, hentikan dan pilih aktivitas lain.

## Kapan mencari dukungan?

Jika kesulitan tidur berlangsung berulang, memengaruhi konsentrasi atau suasana hati, atau kamu mulai khawatir tentang tidur hampir setiap malam, konsultasikan dengan tenaga kesehatan. Ada pendekatan terstruktur untuk masalah tidur yang mungkin lebih sesuai daripada mencoba tips secara acak.

### Referensi
- NHS, [Insomnia](https://www.nhs.uk/conditions/insomnia/)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'kafein-gawai-dan-jadwal-tidur-mahasiswa',
                'title' => 'Kafein, Gawai, dan Jadwal Tidur Mahasiswa',
                'excerpt' => 'Pahami bagaimana kebiasaan sore dan malam mungkin memengaruhi tidur, lalu lakukan eksperimen kecil yang aman.',
                'meta_title' => 'Kafein, Gawai, dan Tidur Mahasiswa',
                'meta_description' => 'Tinjau pengaruh kafein, layar, dan jadwal yang berubah terhadap tidur dengan cara yang realistis.',
                'category' => ArticleCategory::Sleep,
                'body_markdown' => <<<'MARKDOWN'
## Kebiasaan sehari-hari dapat ikut membentuk pola tidur

Belajar sampai larut, minum kopi untuk tetap fokus, atau menggunakan gawai di tempat tidur adalah hal yang umum pada masa kuliah. Pengaruhnya tidak sama pada setiap orang. Kafein dapat membuat sebagian orang lebih sulit tidur, sementara layar bisa memperpanjang aktivitas dan menunda waktu istirahat.

## Coba amati, lalu ubah satu hal

- Catat kapan kamu minum kopi, teh, atau minuman berkafein dan bagaimana tidurmu setelahnya.
- Jika kafein tampak mengganggu, coba kurangi atau hindari pada sore dan malam hari.
- Aktifkan pengingat waktu berhenti belajar, bukan hanya pengingat tenggat.
- Letakkan ponsel sedikit lebih jauh saat rutinitas tidur dimulai jika kamu sering terus menggulir layar.
- Buat jadwal yang memberi cukup waktu tidur, tetapi tetap realistis terhadap kuliah dan pekerjaan.

Kamu tidak harus menghilangkan semua teknologi. Panggilan dengan keluarga, akses materi kuliah, atau aktivitas santai daring dapat menjadi bagian penting hidup. Fokus pada kebiasaan yang benar-benar membuatmu sulit tidur.

## Jangan mengandalkan kafein untuk menggantikan tidur

Minuman berkafein mungkin membuat seseorang merasa lebih terjaga untuk sementara, tetapi bukan pengganti tidur yang cukup. Jika rasa kantuk berat terus terjadi meski sudah memberi waktu tidur, konsultasikan dengan tenaga kesehatan.

### Referensi
- CDC, [About Sleep](https://www.cdc.gov/sleep/about/index.html)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'memahami-kecemasan-dan-kapan-perlu-dukungan',
                'title' => 'Memahami Kecemasan dan Kapan Perlu Dukungan',
                'excerpt' => 'Rasa cemas adalah pengalaman manusia; kenali perbedaan kekhawatiran sesaat dan gejala yang perlu dinilai profesional.',
                'meta_title' => 'Memahami Kecemasan dan Kapan Mencari Bantuan',
                'meta_description' => 'Kenali kecemasan secara umum, dampaknya pada keseharian, dan kapan sebaiknya meminta bantuan profesional.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Cemas sesekali tidak otomatis berarti gangguan kecemasan

Rasa cemas dapat muncul sebelum ujian, presentasi, wawancara, atau keputusan penting. Pada sebagian orang, kecemasan menjadi sangat intens, sulit dikendalikan, berlangsung lama, dan mengganggu kegiatan sehari-hari. Penilaian profesional mempertimbangkan lebih dari satu gejala atau hasil kuis daring.

Gejala kecemasan dapat melibatkan pikiran yang terus khawatir, ketegangan tubuh, sulit berkonsentrasi, gangguan tidur, atau dorongan menghindari situasi tertentu. Gejala ini juga dapat memiliki penyebab lain, sehingga artikel atau asesmen mandiri tidak dapat memastikan diagnosis.

## Apa yang dapat membantu saat ini?

- Sebutkan apa yang kamu rasakan tanpa memberi label pada diri: “Aku merasa khawatir menjelang presentasi.”
- Periksa fakta yang diketahui dan bedakan dari kemungkinan yang belum terjadi.
- Pilih satu langkah yang bisa dilakukan, seperti latihan presentasi atau meminta penjelasan.
- Coba napas perlahan atau grounding jika terasa nyaman.
- Ceritakan kepada orang yang tepercaya, terutama bila kecemasan membuatmu menghindari kegiatan penting.

## Kapan perlu konsultasi?

Jika kecemasan sering terjadi, terasa sulit dikendalikan, menyebabkan penderitaan, atau mengganggu kuliah, pekerjaan, tidur, dan hubungan, bicaralah dengan tenaga kesehatan mental. Perawatan yang efektif tersedia dan pilihan bantuan dapat disesuaikan dengan kebutuhan.

### Referensi
- WHO, [Anxiety Disorders](https://www.who.int/news-room/fact-sheets/detail/anxiety-disorders)
- National Institute of Mental Health, [Anxiety Disorders](https://www.nimh.nih.gov/health/topics/anxiety-disorders)
MARKDOWN,
            ],
            [
                'slug' => 'latihan-napas-perlahan-saat-merasa-tegang',
                'title' => 'Latihan Napas Perlahan saat Merasa Tegang',
                'excerpt' => 'Latihan napas sederhana dapat dicoba sebagai keterampilan pengelolaan stres, selama terasa nyaman bagi tubuh.',
                'meta_title' => 'Latihan Napas Perlahan untuk Mengelola Stres',
                'meta_description' => 'Panduan latihan napas sederhana yang dapat dicoba saat tegang, beserta cara menyesuaikannya jika tidak nyaman.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Napas sebagai jangkar perhatian

Saat tegang, sebagian orang menyadari napasnya menjadi cepat atau dangkal. Latihan napas perlahan dapat membantu mengarahkan perhatian dan memberi jeda. Efeknya tidak sama untuk semua orang; latihan ini bukan pengganti penanganan medis atau psikologis.

## Cara mencoba dengan lembut

1. Duduk dengan posisi yang cukup nyaman dan kaki ditopang.
2. Biarkan bahu rileks tanpa memaksakan postur.
3. Tarik napas dengan lembut melalui hidung jika nyaman.
4. Hembuskan perlahan melalui hidung atau mulut.
5. Jika membantu, hitung dengan nyaman saat menarik dan mengembuskan napas. Tidak perlu memaksakan hitungan tertentu atau mengambil napas terlalu dalam.
6. Lanjutkan sebentar, lalu kembali ke aktivitas saat siap.

Jika pusing, sesak, atau semakin tidak nyaman, hentikan latihan dan bernapas seperti biasa. Pilih cara lain seperti melihat benda di sekitar, berjalan pelan, atau berbicara dengan orang tepercaya.

## Latihan bukan tes keberhasilan

Tujuannya bukan memaksa tubuh langsung tenang. Kamu boleh mencoba di waktu yang relatif nyaman agar mengenali apakah teknik ini cocok sebelum menggunakannya saat stres.

Jika kamu sering mengalami serangan panik, sesak, nyeri dada, atau gejala fisik yang mengkhawatirkan, jangan menganggap semuanya disebabkan kecemasan. Mintalah penilaian tenaga kesehatan, terutama untuk gejala baru atau berat.

### Referensi
- NHS, [Breathing Exercises for Stress](https://www.nhs.uk/mental-health/self-help/guides-tools-and-activities/breathing-exercises-for-stress/)
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
MARKDOWN,
            ],
            [
                'slug' => 'menghadapi-kekhawatiran-tentang-masa-depan',
                'title' => 'Menghadapi Kekhawatiran tentang Masa Depan',
                'excerpt' => 'Saat pikiran berlari ke berbagai kemungkinan, bedakan perencanaan yang membantu dari kekhawatiran yang berputar.',
                'meta_title' => 'Cara Menghadapi Kekhawatiran tentang Masa Depan',
                'meta_description' => 'Langkah praktis membedakan hal yang bisa direncanakan dari kemungkinan yang belum dapat dipastikan.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Pikiran mencoba melindungi, tetapi bisa menjadi melelahkan

Memikirkan kelulusan, pekerjaan, keuangan, atau hubungan adalah hal yang wajar. Namun, kekhawatiran bisa berputar pada pertanyaan “bagaimana jika?” tanpa menghasilkan tindakan yang jelas.

Kamu tidak perlu membuktikan bahwa semua kemungkinan buruk mustahil terjadi. Cobalah mengembalikan perhatian pada informasi yang tersedia dan langkah yang masuk akal saat ini.

## Tiga pertanyaan untuk memilah kekhawatiran

1. **Apa yang benar-benar saya ketahui?** Pisahkan fakta dari dugaan atau prediksi.
2. **Apakah ada tindakan kecil yang bisa dilakukan sekarang?** Misalnya mencari informasi beasiswa atau menghubungi pembimbing.
3. **Jika belum ada tindakan, kapan saya akan meninjau ulang?** Tentukan waktu untuk kembali memikirkan hal itu, lalu lakukan kegiatan yang ada di depanmu.

Kamu juga bisa menuliskan kekhawatiran dan respons yang lebih seimbang. Hindari memaksa diri menjawab setiap pertanyaan yang belum memiliki informasi cukup.

## Jangan hadapi semuanya sendirian

Bercerita kepada teman, keluarga, dosen pembimbing, atau konselor dapat membantu memperjelas pilihan. Jika kekhawatiran terus mengganggu tidur, konsentrasi, atau aktivitas sehari-hari, bantuan profesional patut dipertimbangkan.

### Referensi
- WHO, [Anxiety Disorders](https://www.who.int/news-room/fact-sheets/detail/anxiety-disorders)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'mengurangi-penghindaran-saat-merasa-cemas',
                'title' => 'Mengurangi Penghindaran saat Merasa Cemas',
                'excerpt' => 'Menghindari hal yang menakutkan kadang memberi kelegaan sesaat, tetapi dapat membuat aktivitas penting makin sulit dijalani.',
                'meta_title' => 'Menghadapi Penghindaran akibat Kecemasan',
                'meta_description' => 'Pahami pola menghindar dan cara mengambil langkah kecil yang aman dengan dukungan yang sesuai.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Kelegaan sesaat dan dampak jangka panjang

Saat cemas, membatalkan presentasi atau menunda membuka email mungkin membuat perasaan mereda untuk sementara. Namun, jika penghindaran terus berulang, kesempatan untuk belajar menghadapi situasi tersebut bisa berkurang. Ini bukan tanda kelemahan; penghindaran sering menjadi respons ketika sesuatu terasa mengancam.

## Mulai dengan langkah yang aman dan kecil

- Tentukan situasi spesifik yang ingin kamu hadapi, bukan target besar seperti “tidak boleh cemas lagi”.
- Pecah menjadi beberapa langkah, dari yang lebih ringan hingga yang lebih menantang.
- Pilih langkah yang aman dan sesuai keadaan. Misalnya, membaca instruksi presentasi dahulu sebelum berlatih di depan teman.
- Catat apa yang kamu pelajari, bukan hanya seberapa cemas kamu merasa.
- Minta dukungan jika diperlukan.

Jangan memaksa diri menghadapi situasi yang benar-benar tidak aman atau melampaui kemampuan saat ini. Untuk kecemasan yang berat, terapi dengan tenaga profesional dapat membantu menyusun pendekatan bertahap yang sesuai.

## Kapan meminta bantuan?

Jika rasa cemas membuatmu terus menghindari kelas, pekerjaan, relasi, atau kegiatan penting, pertimbangkan konsultasi. Penanganan dapat mencakup psikoterapi, termasuk pendekatan berbasis bukti yang disesuaikan oleh profesional.

### Referensi
- WHO, [Anxiety Disorders](https://www.who.int/news-room/fact-sheets/detail/anxiety-disorders)
- National Institute of Mental Health, [Anxiety Disorders](https://www.nimh.nih.gov/health/topics/anxiety-disorders)
MARKDOWN,
            ],
            [
                'slug' => 'mencari-bantuan-untuk-kecemasan-bukan-kegagalan',
                'title' => 'Mencari Bantuan untuk Kecemasan Bukan Kegagalan',
                'excerpt' => 'Meminta bantuan adalah langkah yang masuk akal ketika kecemasan mulai mengganggu kehidupan sehari-hari.',
                'meta_title' => 'Kapan Perlu Mencari Bantuan untuk Kecemasan?',
                'meta_description' => 'Kenali tanda bahwa kecemasan perlu dibicarakan dengan profesional dan cara mempersiapkan konsultasi.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Tidak harus menunggu sampai benar-benar kewalahan

Banyak orang mengalami kekhawatiran, ketegangan, atau gejala fisik saat menghadapi tekanan. Pertimbangkan bantuan profesional jika rasa cemas sering muncul, sulit dikendalikan, menyebabkan penderitaan, atau mengganggu kuliah, pekerjaan, tidur, dan hubungan.

Kamu tidak perlu menentukan sendiri apakah gejalamu “cukup serius”. Konsultasi dapat menjadi ruang untuk memahami apa yang terjadi dan pilihan bantuan yang tersedia.

## Persiapkan percakapan pertama

- Tuliskan hal yang paling mengganggu dan kapan biasanya muncul.
- Catat dampaknya pada tidur, aktivitas, konsentrasi, atau hubungan.
- Sebutkan perubahan yang sudah kamu coba dan apakah membantu.
- Siapkan pertanyaan tentang pilihan penanganan, biaya, privasi, dan jadwal.
- Jika sulit menjelaskan, kamu boleh membawa catatan atau meminta orang tepercaya menemani sesuai aturan layanan.

Bantuan dapat tersedia melalui psikolog, psikiater, dokter, konselor kampus, atau layanan kesehatan setempat. Jenis layanan yang tepat bergantung pada kebutuhan dan ketersediaan.

## Jangan jadikan informasi daring sebagai diagnosis

Artikel dan kuis mandiri dapat membantu mengenali pertanyaan yang ingin dibawa ke konsultasi, tetapi tidak dapat memastikan gangguan tertentu. Profesional akan menilai gejala, riwayat, konteks, dan dampaknya.

### Referensi
- WHO, [Anxiety Disorders](https://www.who.int/news-room/fact-sheets/detail/anxiety-disorders)
- National Institute of Mental Health, [Anxiety Disorders](https://www.nimh.nih.gov/health/topics/anxiety-disorders)
MARKDOWN,
            ],
            [
                'slug' => 'mendukung-teman-yang-sedang-cemas',
                'title' => 'Mendukung Teman yang Sedang Cemas',
                'excerpt' => 'Dengarkan tanpa meremehkan, tanyakan dukungan yang dibutuhkan, dan tetap kenali batas kemampuanmu.',
                'meta_title' => 'Cara Mendukung Teman yang Sedang Cemas',
                'meta_description' => 'Pelajari cara mendengarkan teman yang cemas tanpa menghakimi dan kapan membantu menghubungkannya dengan dukungan.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Hadir tidak berarti harus punya semua jawaban

Ketika teman terlihat cemas, kita mungkin ingin segera memberi solusi. Namun, nasihat yang terlalu cepat atau kalimat seperti “jangan dipikirkan” dapat membuatnya merasa tidak dipahami.

Mulailah dengan mendengarkan. Kamu bisa mengatakan, “Kedengarannya ini berat. Kamu ingin bercerita, atau ingin kita memikirkan langkah berikutnya bersama?” Pertanyaan sederhana memberi ruang bagi teman untuk menentukan dukungan yang dibutuhkan.

## Hal yang dapat membantu

- Dengarkan tanpa menyela atau membandingkan dengan pengalaman orang lain.
- Jangan memberi label klinis atau memaksa teman menjelaskan semuanya.
- Tawarkan bantuan konkret, misalnya menemani mencari informasi layanan konseling.
- Tanyakan kembali dengan wajar, tanpa menuntut kabar setiap saat.
- Hormati privasi, kecuali ada kekhawatiran keselamatan yang perlu ditangani dengan bantuan orang dewasa atau profesional tepercaya.

## Jaga batasmu juga

Kamu boleh peduli tanpa menjadi satu-satunya sumber dukungan. Jika masalah teman terasa berat, bantu menghubungkannya dengan orang tepercaya atau tenaga profesional. Kamu tidak harus menyelesaikan situasi itu sendirian.

Jika ada tanda bahwa seseorang berada dalam bahaya segera, utamakan keselamatan dan cari bantuan langsung dari layanan darurat atau orang dewasa/profesional yang dapat bertindak di tempatnya.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Mental Health](https://www.who.int/health-topics/mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'mengenali-pikiran-khawatir-dan-fakta',
                'title' => 'Mengenali Pikiran Khawatir dan Fakta',
                'excerpt' => 'Latihan memisahkan fakta, tafsiran, dan kemungkinan dapat membantu merespons pikiran dengan lebih seimbang.',
                'meta_title' => 'Membedakan Pikiran Khawatir dan Fakta',
                'meta_description' => 'Gunakan pertanyaan sederhana untuk menilai pikiran yang mencemaskan tanpa memaksa diri berpikir positif.',
                'category' => ArticleCategory::Anxiety,
                'body_markdown' => <<<'MARKDOWN'
## Pikiran yang terasa nyata belum tentu merupakan fakta

Saat cemas, sebuah kemungkinan dapat terasa seolah pasti terjadi: “Aku akan gagal,” “Semua orang menilai buruk,” atau “Aku tidak akan sanggup.” Pikiran seperti ini adalah pengalaman mental, bukan bukti otomatis bahwa prediksi itu benar.

Tujuannya bukan menyangkal semua kekhawatiran. Sebagian kekhawatiran memang menunjukkan masalah yang perlu ditangani. Kita ingin menilai pikiran dengan lebih seimbang agar tindakan tidak hanya didorong oleh ketakutan.

## Latihan tiga langkah

1. **Tuliskan pikiran:** “Presentasiku pasti berantakan.”
2. **Pisahkan fakta dari tafsiran:** fakta mungkin kamu belum selesai berlatih; prediksi “pasti berantakan” belum diketahui.
3. **Buat kalimat yang lebih seimbang:** “Aku belum siap sepenuhnya, tetapi aku bisa berlatih bagian pembuka dan meminta umpan balik.”

Tanyakan juga apa yang akan kamu katakan kepada teman dalam situasi serupa. Respons yang seimbang tidak harus optimistis; cukup akurat dan membantu menentukan langkah berikutnya.

## Jika latihan ini tidak cukup

Latihan mandiri dapat membantu sebagian orang, tetapi tidak menggantikan dukungan profesional bila kecemasan berat atau menetap. Jika pikiran mengganggu aktivitas, tidur, atau relasi, pertimbangkan konsultasi dengan tenaga kesehatan mental.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- National Institute of Mental Health, [Anxiety Disorders](https://www.nimh.nih.gov/health/topics/anxiety-disorders)
MARKDOWN,
            ],
            [
                'slug' => 'mengenali-burnout-dan-perbedaannya-dengan-lelah',
                'title' => 'Mengenali Burnout dan Perbedaannya dengan Lelah',
                'excerpt' => 'Burnout bukan sekadar satu hari yang melelahkan; pahami istilahnya dan dampak beban berkepanjangan.',
                'meta_title' => 'Apa Itu Burnout dan Bagaimana Mengenalinya?',
                'meta_description' => 'Pahami konsep burnout, tanda kelelahan berkepanjangan, dan langkah mencari dukungan tanpa mendiagnosis diri.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Burnout adalah istilah yang perlu digunakan dengan tepat

WHO mengklasifikasikan burnout dalam konteks pekerjaan sebagai fenomena yang berkaitan dengan stres kerja kronis yang tidak berhasil dikelola. WHO tidak menggolongkannya sebagai kondisi medis dan definisinya berfokus pada konteks pekerjaan. Istilah ini sering digunakan sehari-hari untuk kuliah atau tanggung jawab lain, tetapi pengalaman mahasiswa tetap perlu dilihat sesuai konteksnya.

Dalam konteks kerja, WHO menjelaskan tiga dimensi: kehabisan energi atau kelelahan, jarak mental atau sikap sinis terhadap pekerjaan, dan berkurangnya efektivitas profesional. Kelelahan biasa dapat membaik setelah pemulihan, sedangkan kesulitan berkepanjangan memerlukan perhatian lebih lanjut.

## Apa yang dapat ditinjau?

- Apakah beban dan tenggat realistis?
- Apakah ada kesempatan beristirahat dan pulih?
- Apakah pembagian tugas atau dukungan perlu dibicarakan?
- Apakah rasa lelah mulai mengganggu tidur, relasi, atau aktivitas sehari-hari?

Langkah awal dapat berupa membicarakan beban dengan pembimbing, atasan, anggota kelompok, atau orang tepercaya. Bila memungkinkan, sepakati prioritas, batas waktu, dan dukungan yang konkret.

## Jangan menyimpulkan sendiri

Kelelahan dan kehilangan motivasi dapat memiliki berbagai penyebab, termasuk kondisi fisik maupun mental. Jika gejala menetap atau mengganggu fungsi sehari-hari, mintalah penilaian profesional.

### Referensi
- WHO, [Burn-out an occupational phenomenon: International Classification of Diseases](https://www.who.int/news/item/28-05-2019-burn-out-an-occupational-phenomenon-international-classification-of-diseases)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'tanda-beban-kuliah-perlu-dibicarakan',
                'title' => 'Tanda Beban Kuliah Perlu Dibicarakan',
                'excerpt' => 'Kenali kapan beban akademik tidak lagi terasa berkelanjutan dan bagaimana membuka percakapan untuk mencari solusi.',
                'meta_title' => 'Kapan Beban Kuliah Perlu Dibicarakan?',
                'meta_description' => 'Kenali tanda beban kuliah terlalu berat dan persiapkan percakapan dengan dosen, pembimbing, atau orang tepercaya.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Perhatikan dampak, bukan hanya jumlah tugas

Beban kuliah menjadi lebih sulit dikelola ketika tidak ada cukup waktu untuk menyelesaikan tugas, tidur, makan, atau memenuhi kebutuhan lain. Kamu mungkin merasa selalu tertinggal, sulit berkonsentrasi, mudah tersinggung, atau kehilangan minat pada kegiatan yang biasanya bermakna. Tanda-tanda ini tidak menentukan diagnosis, tetapi dapat menjadi alasan untuk meninjau situasi.

## Persiapkan percakapan yang konkret

1. Catat tugas dan tenggat yang sedang berjalan.
2. Jelaskan kendala spesifik, misalnya dua tenggat besar pada hari yang sama atau instruksi yang belum jelas.
3. Sampaikan kebutuhan yang realistis: klarifikasi, prioritas, pembagian tugas, atau informasi tentang kebijakan penundaan.
4. Ajukan pertanyaan terbuka: “Pilihan apa yang tersedia agar saya dapat menyelesaikan kewajiban ini dengan baik?”
5. Setelah percakapan, tulis kesepakatan dan langkah berikutnya.

Meminta penyesuaian tidak selalu berarti semua permintaan akan disetujui, tetapi komunikasi lebih awal dapat membantu menemukan pilihan sebelum masalah membesar.

## Cari dukungan bila dampaknya meluas

Jika beban disertai gangguan tidur yang menetap, rasa putus asa, kecemasan berat, atau kesulitan menjalani kegiatan harian, pertimbangkan berbicara dengan konselor atau tenaga kesehatan. Masalah akademik dan kesehatan mental dapat saling memengaruhi; kamu tidak harus menyelesaikannya sendirian.

### Referensi
- WHO, [Mental Health](https://www.who.int/health-topics/mental-health)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'menetapkan-batas-sehat-di-organisasi-dan-kuliah',
                'title' => 'Menetapkan Batas Sehat di Organisasi dan Kuliah',
                'excerpt' => 'Belajar menyampaikan kapasitas dan menegosiasikan komitmen agar kegiatan tetap selaras dengan kebutuhan diri.',
                'meta_title' => 'Cara Menetapkan Batas Sehat saat Kuliah',
                'meta_description' => 'Strategi berkomunikasi tentang kapasitas, menolak tugas tambahan dengan sopan, dan menjaga komitmen.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Batas membantu memperjelas komitmen

Kuliah, organisasi, kerja, dan kehidupan pribadi dapat saling berebut waktu. Menyetujui semua permintaan mungkin terasa lebih mudah pada awalnya, tetapi dapat membuat komitmen utama sulit dipenuhi. Batas bukan hukuman bagi orang lain; batas adalah cara menjelaskan kapasitas dan menjaga kesepakatan.

## Contoh kalimat yang bisa disesuaikan

- “Aku belum bisa memastikan sebelum mengecek tenggat kuliah. Aku kabari setelah melihat jadwalku.”
- “Minggu ini aku bisa membantu bagian A, tetapi belum sanggup mengambil bagian B.”
- “Aku ingin membantu, tetapi tenggat ini bertabrakan dengan komitmen yang sudah ada. Apakah ada alternatif?”
- “Aku bisa mengerjakan ini jika tugas lain dipindahkan. Mana yang paling prioritas?”

Pilih kalimat yang sesuai dengan hubungan dan situasi. Kamu tidak harus menjelaskan seluruh urusan pribadi untuk menyampaikan kapasitas.

## Batas bukan hanya tentang menolak

Batas juga bisa berarti menetapkan jam membalas pesan, meminta pembagian tugas yang jelas, menyepakati waktu istirahat, atau memberi tahu lebih awal ketika tenggat berisiko terlewat.

Jika lingkungan terus menuntut beban yang tidak realistis atau membuatmu merasa tidak aman, carilah dukungan dari pembimbing, pihak kampus, atasan, atau orang tepercaya. Jangan menganggap semua masalah dapat diselesaikan hanya dengan mengatur waktu pribadi.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- Kementerian Kesehatan RI, [Sehat Jiwa untuk Mampu Menghadapi Tantangan Hidup](https://keslan.kemkes.go.id/view_artikel/3990/sehat-jiwa-untuk-mampu-menghadapi-tantangan-hidup)
MARKDOWN,
            ],
            [
                'slug' => 'memulihkan-energi-setelah-masa-kuliah-yang-padat',
                'title' => 'Memulihkan Energi setelah Masa Kuliah yang Padat',
                'excerpt' => 'Pemulihan tidak selalu membutuhkan liburan panjang; mulai dari kebutuhan dasar dan evaluasi beban yang berulang.',
                'meta_title' => 'Cara Memulihkan Energi setelah Masa Kuliah Padat',
                'meta_description' => 'Langkah pemulihan realistis setelah minggu yang padat dan cara mengevaluasi beban yang berulang.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Pemulihan lebih dari sekadar berhenti sebentar

Setelah ujian atau proyek besar, kamu mungkin merasa lelah secara fisik dan mental. Tidak semua kelelahan hilang dalam satu malam. Pemulihan dapat melibatkan tidur, makan teratur, aktivitas yang menyenangkan, hubungan sosial, dan perubahan pada beban yang membuatmu kelelahan.

## Susun pemulihan dalam skala kecil

- Kembali ke pola tidur yang lebih teratur jika jadwal sebelumnya berubah.
- Makan dan minum secara cukup, terutama setelah hari yang padat.
- Lakukan gerak ringan atau aktivitas luar ruang yang sesuai kemampuan.
- Hubungi teman atau keluarga untuk terhubung tanpa harus membahas produktivitas.
- Pilih kegiatan santai yang benar-benar kamu nikmati, bukan yang terasa seperti kewajiban baru.

Setelah lebih pulih, lihat kembali pola yang membuat masa padat terasa berat. Apakah semua tugas perlu dilakukan bersamaan? Apakah pembagian kerja adil? Apakah ada waktu untuk meminta bantuan sebelum tenggat?

## Jika kelelahan tidak membaik

Kelelahan yang menetap dapat memiliki banyak penyebab. Jika kamu terus merasa sangat lelah, kehilangan minat, sulit tidur, atau kesulitan melakukan aktivitas biasa, bicarakan dengan tenaga kesehatan. Jangan menyimpulkan bahwa semuanya hanya karena kurang motivasi.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Mental Health](https://www.who.int/health-topics/mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'dukungan-sosial-saat-merasa-kehabisan-tenaga',
                'title' => 'Dukungan Sosial saat Merasa Kehabisan Tenaga',
                'excerpt' => 'Terhubung dengan orang lain dapat membantu menghadapi masa sulit; dukungan bisa diminta secara spesifik dan sederhana.',
                'meta_title' => 'Meminta Dukungan saat Merasa Kelelahan',
                'meta_description' => 'Cara meminta dukungan emosional atau praktis ketika beban terasa berat, tanpa merasa harus menjelaskan semuanya.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Kamu tidak harus menjelaskan semuanya sekaligus

Ketika lelah, membalas pesan atau bertemu orang bisa terasa berat. Namun, dukungan dari orang tepercaya dapat membantu menghadapi masa sulit. Dukungan tidak harus berupa solusi besar; kadang ditemani makan, mendengarkan, atau membantu menyusun prioritas sudah berarti.

## Buat permintaan yang spesifik

Daripada mengatakan “Aku sedang kacau” jika sulit menjelaskan, kamu bisa mencoba:
- “Bisa dengarkan aku selama sepuluh menit? Aku belum butuh saran.”
- “Aku kewalahan melihat semua tugas. Bisa bantu memilih mana yang harus dikerjakan dulu?”
- “Mau belajar bersama selama satu jam lalu kita istirahat?”
- “Aku butuh bantuan mencari layanan konseling kampus. Bisa temani mencari informasinya?”

Pilih orang yang cukup aman dan menghormati privasimu. Jika satu orang tidak dapat membantu, itu tidak berarti kamu tidak layak mendapat dukungan; mungkin perlu mencari pilihan lain.

## Tetap jaga batas kedua pihak

Teman dapat memberi dukungan, tetapi tidak harus menjadi konselor profesional. Bila kelelahan atau tekanan terus mengganggu kehidupan sehari-hari, pertimbangkan bantuan dari tenaga kesehatan atau layanan kampus.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Mental Health](https://www.who.int/health-topics/mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'kembali-menemukan-makna-di-tengah-tanggung-jawab',
                'title' => 'Kembali Menemukan Makna di Tengah Tanggung Jawab',
                'excerpt' => 'Saat aktivitas terasa hanya sebagai daftar kewajiban, meninjau nilai pribadi dapat membantu memilih langkah kecil yang bermakna.',
                'meta_title' => 'Menemukan Makna di Tengah Kesibukan',
                'meta_description' => 'Refleksi sederhana tentang nilai pribadi untuk membantu memilih tindakan yang bermakna di tengah kesibukan.',
                'category' => ArticleCategory::Burnout,
                'body_markdown' => <<<'MARKDOWN'
## Tidak semua aktivitas harus terasa menyenangkan

Kuliah atau pekerjaan kadang berisi tugas yang membosankan dan melelahkan. Namun, ketika hampir semua hal terasa hampa, kamu mungkin perlu berhenti sejenak untuk melihat apakah jadwal, kebutuhan, atau dukungan perlu berubah.

Meninjau nilai pribadi bukan berarti menemukan satu tujuan hidup yang besar. Nilai bisa berupa belajar, keluarga, kreativitas, kontribusi, kemandirian, atau hubungan yang baik. Nilai adalah arah yang membantu memilih tindakan, bukan target yang harus dicapai sempurna.

## Latihan refleksi singkat

1. Pikirkan satu hal yang penting bagimu dalam hubungan, belajar, atau kehidupan sehari-hari.
2. Tanyakan tindakan kecil apa yang bisa menunjukkan nilai itu hari ini.
3. Pastikan tindakan tersebut realistis dengan energi dan waktu yang tersedia.
4. Setelah melakukannya, perhatikan apakah tindakan itu terasa selaras—tanpa menuntut diri harus langsung bahagia.

Contohnya, jika hubungan penting bagimu, kamu bisa mengirim pesan singkat kepada teman. Jika belajar penting, kamu bisa mengajukan satu pertanyaan yang selama ini ditunda. Langkah kecil tidak menyelesaikan semua masalah, tetapi dapat memberi arah.

## Jika kehilangan minat menetap

Kehilangan minat atau rasa lelah yang berlangsung lama dapat memiliki berbagai penyebab. Jika perubahan suasana hati mengganggu fungsi sehari-hari, konsultasikan dengan tenaga kesehatan. Jangan menyalahkan diri karena tidak dapat menyelesaikan semuanya hanya dengan menemukan motivasi.

### Referensi
- WHO, [Doing What Matters in Times of Stress](https://www.who.int/publications/i/item/9789240003927)
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
MARKDOWN,
            ],
            [
                'slug' => 'kapan-perlu-mencari-bantuan-profesional',
                'title' => 'Kapan Perlu Mencari Bantuan Profesional?',
                'excerpt' => 'Kenali perubahan yang menetap atau mengganggu aktivitas dan persiapkan langkah untuk mengakses dukungan profesional.',
                'meta_title' => 'Kapan Perlu Mencari Bantuan Kesehatan Mental?',
                'meta_description' => 'Panduan umum mengenali kapan masalah kesehatan mental perlu dibicarakan dengan tenaga profesional.',
                'category' => ArticleCategory::Stress,
                'body_markdown' => <<<'MARKDOWN'
## Meminta bantuan tidak harus menunggu krisis

Setiap orang dapat mengalami hari yang sulit. Namun, jika perubahan suasana hati, kecemasan, tidur, nafsu makan, konsentrasi, atau minat berlangsung menetap dan mengganggu kehidupan sehari-hari, bantuan profesional patut dipertimbangkan.

NIMH menyarankan mencari bantuan ketika gejala berat atau mengganggu berlangsung sekitar dua minggu atau lebih, misalnya kesulitan tidur, sulit berkonsentrasi, kehilangan minat, atau tidak mampu menyelesaikan kegiatan biasa. Angka waktu ini bukan aturan untuk menunda bantuan: kamu boleh meminta pertolongan lebih awal jika merasa perlu.

## Cara memulai

- Catat perubahan yang kamu alami dan dampaknya.
- Hubungi layanan konseling kampus, puskesmas, dokter, psikolog, atau psikiater sesuai kebutuhan dan akses.
- Tanyakan biaya, jadwal, kerahasiaan, dan pilihan layanan.
- Bawa catatan atau pertanyaan jika sulit menjelaskan secara langsung.
- Jika pengalaman pertama tidak cocok, tanyakan pilihan lain yang tersedia.

Artikel, percakapan dengan teman, atau asesmen mandiri dapat menjadi langkah awal untuk memahami kebutuhan, tetapi tidak menggantikan penilaian profesional.

## Jika membutuhkan bantuan segera

Jika kamu atau orang lain berada dalam bahaya langsung, hubungi layanan darurat setempat atau pergi ke fasilitas kesehatan terdekat. Cari orang tepercaya yang dapat menemani dan membantu mengakses pertolongan.

### Referensi
- National Institute of Mental Health, [Caring for Your Mental Health](https://www.nimh.nih.gov/health/topics/caring-for-your-mental-health)
- WHO, [Mental Disorders](https://www.who.int/news-room/fact-sheets/detail/mental-disorders)
MARKDOWN,
            ],
        ];

        $publishedAt = now()->utc()->subDays(count($articles));

        foreach ($articles as $index => $article) {
            Article::query()->updateOrCreate(
                ['slug' => $article['slug']],
                $article + [
                    'related_assessment_slug' => null,
                    'author_name' => 'Tim Edukasi RelaxBoss',
                    'status' => ArticleStatus::Published,
                    'published_at' => $publishedAt->copy()->addDays($index),
                ],
            );
        }

        $this->command?->info(sprintf(
            'ArticleContentSeeder selesai: %d artikel diterbitkan/diperbarui.',
            count($articles),
        ));
    }
}
