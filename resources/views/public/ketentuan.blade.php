@extends('layouts.public')

@section('title', 'Ketentuan Penggunaan | RelaxBoss')
@section('description', 'Aturan pemakaian RelaxBoss: bukan layanan medis, tanggung jawab akun, batas pemakaian wajar, dan keterbatasan RelaxMate.')

@section('content')
    {{-- DRAF SEMENTARA: dibaca founder sebelum launch (TASK-030). --}}
    <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
        <h1>Ketentuan penggunaan</h1>
        <p class="mt-4 text-text-secondary">
            Dengan memakai RelaxBoss, kamu menyetujui ketentuan berikut.
        </p>

        <section class="mt-8" aria-labelledby="layanan">
            <h2 id="layanan">Bukan layanan medis</h2>
            <p class="mt-3 text-text-secondary">
                RelaxBoss adalah alat bantu mengenali dan mencatat kondisimu. RelaxBoss bukan psikolog, bukan layanan medis, dan tidak memberi diagnosis.
                Hasil Asesmen adalah gambaran awal. Untuk penilaian yang tepat, bicarakan dengan tenaga profesional.
                Kalau kamu butuh bantuan sekarang, lihat halaman <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Konsultasi Profesional</a>.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="relaxmate">
            <h2 id="relaxmate">Tentang RelaxMate</h2>
            <p class="mt-3 text-text-secondary">
                RelaxMate adalah teman bicara berbasis AI. Balasannya bisa keliru atau kurang tepat, dan tidak menggantikan nasihat profesional.
                Dalam keadaan darurat, jangan mengandalkan RelaxMate; hubungi bantuan atau orang yang kamu percaya.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="akun">
            <h2 id="akun">Akunmu</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-text-secondary">
                <li>Isi data akun dengan benar dan jaga kerahasiaan kata sandimu.</li>
                <li>Kamu bertanggung jawab atas aktivitas yang terjadi lewat akunmu.</li>
                <li>Akun dapat dinonaktifkan bila dipakai untuk menyalahgunakan layanan.</li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="wajar">
            <h2 id="wajar">Pemakaian yang wajar</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-text-secondary">
                <li>Jangan mencoba mengganggu, membobol, atau membebani layanan secara berlebihan.</li>
                <li>Jangan memasukkan data pribadi orang lain tanpa izin mereka.</li>
                <li>Ada batas jumlah percobaan dan pesan untuk menjaga layanan tetap tersedia bagi semua.</li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="ketersediaan">
            <h2 id="ketersediaan">Ketersediaan layanan</h2>
            <p class="mt-3 text-text-secondary">
                Layanan dapat berubah, terhenti sementara, atau berhenti sewaktu-waktu. Kami berusaha menjaganya tetap berjalan, tetapi tidak menjanjikan layanan tanpa gangguan.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="privasi">
            <h2 id="privasi">Privasi dan perubahan</h2>
            <p class="mt-3 text-text-secondary">
                Cara kami memperlakukan datamu dijelaskan di <a href="{{ url('/privasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Kebijakan privasi</a>.
                Ketentuan ini dapat diperbarui, dan versi terbaru selalu ada di halaman ini.
            </p>
        </section>
    </article>
@endsection
