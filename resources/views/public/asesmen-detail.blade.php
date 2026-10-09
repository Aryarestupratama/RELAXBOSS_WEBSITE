@extends('layouts.public')

@section('title', \Illuminate\Support\Str::limit($assessment['name'], 41, '…').' | Asesmen RelaxBoss')
@section('description', \Illuminate\Support\Str::limit(strip_tags($assessment['description']), 155))

@section('content')
    @php
        $img = asset('images/asesmen');
        // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
        $circles = ['bg-mood-yellow text-text', 'bg-mood-green text-text', 'bg-brand text-on-primary'];
    @endphp

    {{-- Kepala --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto max-w-6xl px-4 py-10 lg:py-14">
            <a href="{{ url('/asesmen') }}" class="inline-flex min-h-11 items-center gap-2 font-medium text-brand-strong underline underline-offset-4">
                <x-icon name="arrow-left" class="size-4" />
                Semua asesmen
            </a>

            <div class="mt-4 grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">{{ $assessment['name'] }}</h1>

                    @if ($assessment['is_demo'])
                        <p data-load class="mt-3 text-sm text-warning">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
                    @endif

                    <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">{{ $assessment['description'] }}</p>

                    <p data-load class="mt-5 flex flex-wrap items-center gap-x-6 gap-y-1 text-text-secondary">
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="clock" class="size-5 text-brand-strong" />
                            Sekitar {{ $assessment['estimated_minutes'] }} menit
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <x-icon name="list-checks" class="size-5 text-brand-strong" />
                            {{ $assessment['question_count'] }} pertanyaan
                        </span>
                    </p>

                    <div data-load class="mt-8 flex flex-wrap gap-3">
                        <x-button-link href="{{ url('/app/asesmen/'.$assessment['slug']) }}">Mulai asesmen</x-button-link>
                        @guest
                            <x-button-link href="{{ url('/daftar') }}" variant="outline">Daftar gratis</x-button-link>
                        @endguest
                    </div>
                    @guest
                        <p data-load class="mt-4 max-w-prose text-sm text-text-secondary">
                            Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.
                        </p>
                    @endguest
                </div>

                <div data-load="pop" class="mx-auto w-full max-w-md">
                    <div class="grid aspect-square place-items-center overflow-hidden rounded-xl border border-border bg-card p-6 shadow-card">
                        <img src="{{ $img }}/asesmen-detail.webp" width="900" height="900" fetchpriority="high"
                             alt="RelaxMate, robot pendamping, bersandar di papan catatan besar berisi tiga centang, ditemani dua karakter perasaan."
                             class="h-auto w-full max-w-72 object-contain">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Yang dilihat --}}
    @if (count($assessment['sub_scales']) > 0)
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="aspek">
            <h2 id="aspek" data-reveal class="text-2xl md:text-3xl">Yang dilihat</h2>
            <p data-reveal class="mt-2 text-text-secondary">Hasilmu ditampilkan terpisah untuk tiap aspek berikut.</p>
            <ul data-stagger class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($assessment['sub_scales'] as $subScale)
                    <li data-item data-lift class="rounded-xl border border-border bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                        <span class="grid size-10 place-items-center rounded-full {{ $circles[$loop->index % 3] }}" aria-hidden="true">
                            <svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M12 2l1.8 6.5L20 10l-6.2 1.5L12 18l-1.8-6.5L4 10l6.2-1.5z"/></svg>
                        </span>
                        <h3 class="mt-4">{{ $subScale }}</h3>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Cara mengerjakan --}}
    <section class="bg-neutral-soft" aria-labelledby="cara">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 id="cara" data-reveal class="text-2xl md:text-3xl">Cara mengerjakan</h2>
            <div data-reveal class="mt-8 rounded-xl border border-border bg-card p-6 shadow-card lg:p-8">
                @if (filled($assessment['instructions']))
                    <p class="max-w-prose text-text-secondary">{{ $assessment['instructions'] }}</p>
                @endif
                <p class="@if (filled($assessment['instructions'])) mt-3 @endif max-w-prose text-text-secondary">Satu pertanyaan tampil pada satu waktu. Tidak ada jawaban benar atau salah, jawab sesuai keadaanmu.</p>

                @if (count($assessment['options']) > 0)
                    <h3 class="mt-6">Pilihan jawaban</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($assessment['options'] as $option)
                            <li class="rounded-full border border-border bg-neutral-soft px-4 py-2 text-sm font-medium text-text">{{ $option }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    {{-- Tentang instrumen: hanya bila sumber atau validasi terisi dan bukan Instrumen contoh (RULE-049) --}}
    @if (filled($assessment['source']) || filled($assessment['validated_by']))
        <section class="mx-auto max-w-6xl px-4 pt-12 md:pt-16" aria-labelledby="sumber">
            <h2 id="sumber" data-reveal class="text-2xl md:text-3xl">Tentang instrumen</h2>
            <dl data-reveal class="mt-4 max-w-prose space-y-3 text-text-secondary">
                @if (filled($assessment['source']))
                    <div>
                        <dt class="text-sm">Sumber</dt>
                        <dd class="text-text">{{ $assessment['source'] }}</dd>
                    </div>
                @endif
                @if (filled($assessment['validated_by']))
                    <div>
                        <dt class="text-sm">Divalidasi oleh</dt>
                        <dd class="text-text">{{ $assessment['validated_by'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif

    {{-- Disclaimer --}}
    <section class="mx-auto max-w-6xl px-4 pt-12 md:pt-16" aria-label="Catatan penting">
        <div data-reveal class="mx-auto max-w-3xl">
            <x-disclaimer>
                Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
            </x-disclaimer>
            <p class="mt-4">
                <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Bicara dengan profesional</a>
            </p>
        </div>
    </section>

    {{-- Penutup untuk tamu --}}
    @guest
        <section class="mx-auto max-w-6xl px-4 pt-14" aria-labelledby="judul-penutup">
            <div data-reveal class="text-center">
                <h2 id="judul-penutup" class="text-[1.75rem] md:text-5xl">Mulai dari satu langkah kecil.</h2>
                <p class="mx-auto mt-4 max-w-prose text-text-secondary">Mulai dari asesmen atau catat mood pertamamu hari ini.</p>
                <div class="mt-8"><x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link></div>
            </div>
        </section>
    @endguest

    {{-- Bantuan --}}
    <section class="mx-auto max-w-6xl px-4 py-12" aria-label="Bantuan">
        <div data-reveal class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-crisis-bg p-5 text-crisis-text">
            <div>
                <h2 class="text-lg">Butuh bantuan sekarang?</h2>
                <p>Kamu tidak harus menghadapi ini sendirian.</p>
            </div>
            <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-2">Lihat halaman Konsultasi Profesional</a>
        </div>
    </section>
@endsection
