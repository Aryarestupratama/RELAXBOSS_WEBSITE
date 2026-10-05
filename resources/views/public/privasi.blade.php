@extends('layouts.public')

@section('title', 'Kebijakan Privasi | RelaxBoss')
@section('description', 'Data apa yang disimpan RelaxBoss, bagaimana dilindungi, kapan dikirim ke penyedia AI, siapa yang dapat meninjau, dan cara menghapus akunmu.')

@section('content')
    {{-- DRAF SEMENTARA: dibaca founder dan ditelaah sebelum launch (TASK-030, OQ-5). --}}
    <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
        <h1>Kebijakan privasi</h1>
        <p class="mt-4 text-text-secondary">
            Halaman ini menjelaskan data apa yang kami simpan dan bagaimana data itu diperlakukan. Kami berusaha menuliskannya dengan bahasa sehari-hari.
        </p>

        <section class="mt-8" aria-labelledby="data">
            <h2 id="data">Data yang kami simpan</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-text-secondary">
                <li>Akun: nama, email, kata sandi (disimpan dalam bentuk hash), serta jurusan dan kampus bila kamu mengisinya.</li>
                <li>Catatan mood dan catatan singkat yang kamu tulis.</li>
                <li>Jawaban dan hasil Asesmen, termasuk jawaban pertanyaan konteks bila kamu mengisinya.</li>
                <li>Percakapan dengan RelaxMate.</li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="enkripsi">
            <h2 id="enkripsi">Cara data dilindungi</h2>
            <p class="mt-3 text-text-secondary">
                Isi yang sensitif (catatan mood, jawaban Asesmen, percakapan, dan rekomendasi AI) disimpan terenkripsi di level aplikasi.
                Angka skor, kategori hasil, dan skala mood disimpan tanpa enkripsi agar bisa dihitung dan ditampilkan sebagai grafik.
                Kami tidak mencatat isi pesan, catatan, jawaban, email, atau kata sandi di log sistem.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="ai">
            <h2 id="ai">RelaxMate dan penyedia AI</h2>
            <p class="mt-3 text-text-secondary">
                RelaxMate dan Rekomendasi AI memakai penyedia AI pihak ketiga. Hanya setelah kamu menyetujuinya, isi percakapanmu (atau hasil Asesmenmu)
                dan jurusanmu, bila kamu isi, dikirim ke penyedia tersebut. Nama, email, dan kampusmu tidak dikirim.
                Jangan membagikan data yang sangat pribadi seperti alamat atau nomor identitas.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="tinjauan">
            <h2 id="tinjauan">Tinjauan oleh pengelola</h2>
            <p class="mt-3 text-text-secondary">
                Pengelola RelaxBoss dapat membaca isi percakapan dan rekomendasi AI untuk menjaga kualitas dan keselamatan, tanpa identitasmu:
                tanpa nama, email, jurusan, dan kampus, dengan tanggal tanpa jam. Tinjauan ini hanya baca dan setiap pembukaannya tercatat.
                Pengelola tidak melihat catatan moodmu, jawaban Asesmen per butir, maupun jawaban pertanyaan konteksmu.
                Isi percakapan yang kamu tulis bisa saja memuat petunjuk tentang siapa dirimu, jadi hindari menyebut nama atau tempat yang mengenali kamu.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="pelatihan">
            <h2 id="pelatihan">Persetujuan pelatihan model AI</h2>
            <p class="mt-3 text-text-secondary">
                Kami menanyakan secara terpisah apakah percakapan dan hasil asesmenmu boleh dipakai untuk melatih model AI di masa depan.
                Kamu harus memilih sendiri, tanpa pilihan awal. Menolak tidak memengaruhi akses ke fitur apa pun, dan pilihanmu bisa diubah kapan saja di pengaturan akun.
                Data yang diekspor tidak memuat nama, email, jurusan, dan kampus, tetapi termasuk percakapan yang menyentuh topik sensitif seperti menyakiti diri.
                Data yang sudah diekspor tidak bisa ditarik kembali.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="hapus">
            <h2 id="hapus">Menghapus akun</h2>
            <p class="mt-3 text-text-secondary">
                Kamu bisa menghapus akunmu kapan saja di halaman Akun. Seluruh datamu dihapus permanen dan tidak bisa dikembalikan.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="cadangan">
            <h2 id="cadangan">Cadangan</h2>
            <p class="mt-3 text-text-secondary">
                Kami menyimpan cadangan database selama {{ $backupDays }} hari. Data yang sudah kamu hapus bisa masih ada di cadangan sampai masa itu berakhir.
            </p>
        </section>

        <div class="mt-10">
            <x-disclaimer>
                RelaxBoss bukan psikolog atau layanan medis. Lihat juga <a href="{{ url('/ketentuan') }}" class="font-medium text-brand-strong underline underline-offset-2">Ketentuan penggunaan</a>.
            </x-disclaimer>
        </div>
    </article>
@endsection
