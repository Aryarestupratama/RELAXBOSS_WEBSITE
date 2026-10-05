@extends('layouts.public')

@section('title', 'Tentang RelaxBoss')
@section('description', 'RelaxBoss membantu mahasiswa mengenali dan mengelola stres lewat asesmen, mood tracker, dan RelaxMate, teman bicara berbasis AI.')

@section('content')
    {{-- DRAF SEMENTARA: dibaca founder sebelum launch (TASK-030). --}}
    <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
        <h1>Tentang RelaxBoss</h1>

        <section class="mt-8" aria-labelledby="visi">
            <h2 id="visi">Visi</h2>
            <p class="mt-3 text-text-secondary">
                RelaxBoss ingin membantu mahasiswa mengenali dan mengelola stres lebih awal, dengan cara yang sederhana, gratis, dan ramah.
                Kami percaya kamu yang memegang kendali atas kondisimu, bukan sebaliknya.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="fitur">
            <h2 id="fitur">Yang bisa kamu lakukan</h2>
            <ul class="mt-3 space-y-3 text-text-secondary">
                <li><strong class="font-medium text-text">Asesmen.</strong> Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.</li>
                <li><strong class="font-medium text-text">Mood Tracker.</strong> Catat perasaanmu dari hari ke hari dan lihat polanya.</li>
                <li><strong class="font-medium text-text">RelaxMate.</strong> Teman bicara berbasis AI untuk bercerita kapan saja. Bukan psikolog atau layanan medis.</li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="nama">
            <h2 id="nama">Tentang nama</h2>
            <p class="mt-3 text-text-secondary">
                "Relax" adalah ajakan untuk tenang. "Boss" mengingatkan bahwa kamulah yang memegang kendali atas stresmu.
            </p>
        </section>

        {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
        @if (filled($umb))
            <section class="mt-8" aria-labelledby="umb">
                <h2 id="umb">Kaitan akademik</h2>
                <p class="mt-3 text-text-secondary">{{ $umb }}</p>
            </section>
        @endif

        <div class="mt-10">
            <x-disclaimer>
                RelaxBoss bukan psikolog atau layanan medis. Kalau kamu butuh bantuan,
                <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">lihat halaman Konsultasi Profesional</a>.
            </x-disclaimer>
        </div>

        <div class="mt-8">
            <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
        </div>
    </article>
@endsection
