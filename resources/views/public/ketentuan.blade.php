@extends('layouts.public')

@section('title', 'Ketentuan Penggunaan | RelaxBoss')
@section('description', 'Aturan pemakaian RelaxBoss: bukan layanan medis, tanggung jawab akun, batas pemakaian wajar, dan keterbatasan RelaxMate.')

@section('content')
    {{-- DRAF SEMENTARA: dibaca founder sebelum launch (TASK-030). --}}
    @php
        $toc = [
            'layanan' => 'Bukan layanan medis',
            'relaxmate' => 'Tentang RelaxMate',
            'akun' => 'Akunmu',
            'wajar' => 'Pemakaian yang wajar',
            'ketersediaan' => 'Ketersediaan layanan',
            'privasi' => 'Privasi dan perubahan',
        ];
    @endphp

    <x-legal-page title="Ketentuan penggunaan"
                  intro="Dengan memakai RelaxBoss, kamu menyetujui ketentuan berikut."
                  image="ketentuan-hero.webp"
                  image-alt="RelaxMate, robot pendamping, memegang selembar kertas bertanda hati kecil."
                  :toc="$toc">
        <x-legal-section id="layanan" :title="$toc['layanan']" icon="heart" tone="yellow">
            <div role="note" class="rounded-lg border border-border border-l-4 border-l-brand-strong bg-card p-5 shadow-card">
                <p>
                    RelaxBoss adalah alat bantu mengenali dan mencatat kondisimu. RelaxBoss bukan psikolog, bukan layanan medis, dan tidak memberi diagnosis.
                    Hasil Asesmen adalah gambaran awal. Untuk penilaian yang tepat, bicarakan dengan tenaga profesional.
                    Kalau kamu butuh bantuan sekarang, lihat halaman <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Konsultasi Profesional</a>.
                </p>
            </div>
        </x-legal-section>

        <x-legal-section id="relaxmate" :title="$toc['relaxmate']" icon="message-circle" tone="green">
            <p>
                RelaxMate adalah teman bicara berbasis AI. Balasannya bisa keliru atau kurang tepat, dan tidak menggantikan nasihat profesional.
                Dalam keadaan darurat, jangan mengandalkan RelaxMate; hubungi bantuan atau orang yang kamu percaya.
            </p>
        </x-legal-section>

        <x-legal-section id="akun" :title="$toc['akun']" icon="user" tone="brand">
            <ul class="space-y-3">
                @foreach ([
                    'Isi data akun dengan benar dan jaga kerahasiaan kata sandimu.',
                    'Kamu bertanggung jawab atas aktivitas yang terjadi lewat akunmu.',
                    'Akun dapat dinonaktifkan bila dipakai untuk menyalahgunakan layanan.',
                ] as $item)
                    <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand-strong" aria-hidden="true"></span><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        </x-legal-section>

        <x-legal-section id="wajar" :title="$toc['wajar']" icon="shield-check" tone="yellow">
            <ul class="space-y-3">
                @foreach ([
                    'Jangan mencoba mengganggu, membobol, atau membebani layanan secara berlebihan.',
                    'Jangan memasukkan data pribadi orang lain tanpa izin mereka.',
                    'Ada batas jumlah percobaan dan pesan untuk menjaga layanan tetap tersedia bagi semua.',
                ] as $item)
                    <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand-strong" aria-hidden="true"></span><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        </x-legal-section>

        <x-legal-section id="ketersediaan" :title="$toc['ketersediaan']" icon="refresh-cw" tone="green">
            <p>
                Layanan dapat berubah, terhenti sementara, atau berhenti sewaktu-waktu. Kami berusaha menjaganya tetap berjalan, tetapi tidak menjanjikan layanan tanpa gangguan.
            </p>
        </x-legal-section>

        <x-legal-section id="privasi" :title="$toc['privasi']" icon="file-text" tone="brand">
            <p>
                Cara kami memperlakukan datamu dijelaskan di <a href="{{ url('/privasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Kebijakan privasi</a>.
                Ketentuan ini dapat diperbarui, dan versi terbaru selalu ada di halaman ini.
            </p>
        </x-legal-section>
    </x-legal-page>
@endsection
