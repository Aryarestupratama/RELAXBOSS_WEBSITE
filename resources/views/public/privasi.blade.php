@extends('layouts.public')

@section('title', 'Kebijakan Privasi | RelaxBoss')
@section('description', 'Data apa yang disimpan RelaxBoss, bagaimana dilindungi, kapan dikirim ke penyedia AI, siapa yang dapat meninjau, dan cara menghapus akunmu.')

@section('content')
    {{-- Teks final untuk fitur yang berjalan. Telaah hukum (OQ-5) wajib selesai sebelum persetujuan pelatihan dipakai nyata (RULE-073). --}}
    @php
        $toc = [
            'data' => 'Data yang kami simpan',
            'enkripsi' => 'Cara data dilindungi',
            'ai' => 'RelaxMate dan penyedia AI',
            'tinjauan' => 'Tinjauan oleh pengelola',
            'pelatihan' => 'Persetujuan pelatihan model AI',
            'hapus' => 'Menghapus akun',
            'cadangan' => 'Cadangan',
        ];
    @endphp

    <x-legal-page title="Kebijakan privasi"
                  intro="Halaman ini menjelaskan data apa yang kami simpan dan bagaimana data itu diperlakukan. Kami berusaha menuliskannya dengan bahasa sehari-hari."
                  :toc="$toc"
                  active="privasi"
                  updated="19 Oktober 2026"
                  :summary="[
                      ['icon' => 'shield', 'title' => 'Isi sensitif dienkripsi', 'text' => 'Catatan mood, jawaban Asesmen, dan percakapan disimpan terenkripsi.'],
                      ['icon' => 'eye', 'title' => 'Identitasmu tidak dikirim ke AI', 'text' => 'Nama, email, dan kampusmu tidak dikirim ke penyedia AI.'],
                      ['icon' => 'trash-2', 'title' => 'Hapus akun kapan saja', 'text' => 'Datamu dihapus permanen; salinan di cadangan hilang saat masa simpannya berakhir.'],
                  ]">
        <x-legal-section :number="1" id="data" :title="$toc['data']" icon="database" tone="peach">
            <ul class="space-y-3">
                @foreach ([
                    'Akun: nama, email, kata sandi (disimpan dalam bentuk hash), serta jurusan dan kampus bila kamu mengisinya.',
                    'Catatan mood dan catatan singkat yang kamu tulis.',
                    'Jawaban dan hasil Asesmen, termasuk jawaban pertanyaan konteks bila kamu mengisinya.',
                    'Percakapan dengan RelaxMate.',
                ] as $item)
                    <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        </x-legal-section>

        <x-legal-section :number="2" id="enkripsi" :title="$toc['enkripsi']" icon="shield" tone="mint">
            <p>
                Isi yang sensitif (catatan mood, jawaban Asesmen, percakapan, dan rekomendasi AI) disimpan terenkripsi di level aplikasi.
                Angka skor, kategori hasil, dan skala mood disimpan tanpa enkripsi agar bisa dihitung dan ditampilkan sebagai grafik.
                Kami tidak mencatat isi pesan, catatan, jawaban, email, atau kata sandi di log sistem.
            </p>
        </x-legal-section>

        <x-legal-section :number="3" id="ai" :title="$toc['ai']" icon="message-circle" tone="lavender">
            <p>
                RelaxMate dan Rekomendasi AI memakai Groq, penyedia AI pihak ketiga yang servernya berada di luar Indonesia.
                Hanya setelah kamu menyetujuinya, isi percakapanmu (atau hasil Asesmenmu)
                dan jurusanmu, bila kamu isi, dikirim ke penyedia tersebut. Nama, email, dan kampusmu tidak dikirim.
                Catatan moodmu tidak dikirim ke penyedia AI.
                Jangan membagikan data yang sangat pribadi seperti alamat atau nomor identitas.
            </p>
        </x-legal-section>

        <x-legal-section :number="4" id="tinjauan" :title="$toc['tinjauan']" icon="eye" tone="peach">
            <p>
                Pengelola RelaxBoss dapat membaca isi percakapan dan rekomendasi AI untuk menjaga kualitas dan keselamatan, tanpa identitasmu:
                tanpa nama, email, jurusan, dan kampus, dengan tanggal tanpa jam. Tinjauan ini hanya baca dan setiap pembukaannya tercatat.
                Pengelola tidak melihat catatan moodmu, jawaban Asesmen per butir, maupun jawaban pertanyaan konteksmu.
                Isi percakapan yang kamu tulis bisa saja memuat petunjuk tentang siapa dirimu, jadi hindari menyebut nama atau tempat yang mengenali kamu.
            </p>
        </x-legal-section>

        <x-legal-section :number="5" id="pelatihan" :title="$toc['pelatihan']" icon="clipboard-check" tone="mint">
            <div role="note" class="rounded-xl border-l-4 border-primary bg-surface-alt p-5 text-text">
                <p>
                    Kami menanyakan secara terpisah apakah percakapan dan hasil asesmenmu boleh dipakai untuk melatih model AI di masa depan.
                    Kamu harus memilih sendiri, tanpa pilihan awal. Menolak tidak memengaruhi akses ke fitur apa pun, dan pilihanmu bisa diubah kapan saja di pengaturan akun.
                    Data yang diekspor tidak memuat nama, email, jurusan, dan kampus, tetapi termasuk percakapan yang menyentuh topik sensitif seperti menyakiti diri.
                    Data yang sudah diekspor tidak bisa ditarik kembali.
                </p>
            </div>
        </x-legal-section>

        <x-legal-section :number="6" id="hapus" :title="$toc['hapus']" icon="trash-2" tone="lavender">
            <p>Kamu bisa menghapus akunmu kapan saja di halaman Akun. Seluruh datamu dihapus permanen dan tidak bisa dikembalikan.</p>
        </x-legal-section>

        <x-legal-section :number="7" id="cadangan" :title="$toc['cadangan']" icon="archive" tone="peach">
            <p>Kami menyimpan cadangan database selama {{ $backupDays }} hari. Data yang sudah kamu hapus bisa masih ada di cadangan sampai masa itu berakhir.</p>
        </x-legal-section>

        <x-slot:after>
            <x-inline-note>RelaxBoss bukan psikolog atau layanan medis.</x-inline-note>
        </x-slot:after>
    </x-legal-page>
@endsection
