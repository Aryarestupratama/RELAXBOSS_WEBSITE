@extends('layouts.public')

@section('title', 'Daftar Asesmen | RelaxBoss')
@section('description', 'Pilih asesmen untuk mengenali kondisimu: stres, kecemasan, dan kelelahan. Gratis, hasilnya bisa kamu lihat lagi kapan saja.')

@section('content')
    @php
        $img = asset('images/asesmen');
        // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
        $accents = [
            ['circle' => 'bg-mood-yellow text-text'],
            ['circle' => 'bg-mood-green text-text'],
            ['circle' => 'bg-brand text-on-primary'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-2 lg:py-16">
            <div>
                <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">Asesmen</h1>
                <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">
                    Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Pilih asesmen yang ingin kamu coba,
                    lalu masuk atau daftar gratis untuk mengerjakannya.
                </p>
                @guest
                    <div data-load class="mt-8 flex flex-wrap gap-3">
                        <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
                        <x-button-link href="{{ url('/masuk') }}" variant="outline">Masuk</x-button-link>
                    </div>
                @endguest
            </div>

            <div data-load="pop" class="relative mx-auto w-full max-w-md">
                <div class="overflow-hidden rounded-xl border border-border bg-neutral-soft shadow-card">
                    <img src="{{ $img }}/asesmen-hero.webp" width="1200" height="900" fetchpriority="high"
                         alt="RelaxMate, robot pendamping, memegang papan catatan dan pensil di antara karakter perasaan berwarna biru, kuning, merah, dan hijau."
                         class="h-auto w-full">
                </div>
                {{-- Label emosi: hiasan, dipasang lewat kode agar teks tajam --}}
                <div class="hidden sm:block" aria-hidden="true">
                    <span data-float class="absolute top-4 left-4 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-red"></span>Stres</span>
                    <span data-float class="absolute right-4 bottom-4 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-green"></span>Lega</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Daftar asesmen --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-label="Daftar asesmen">
        @if ($assessments->isEmpty())
            <div data-reveal="pop" class="mx-auto flex max-w-xl flex-col items-center rounded-xl border border-border bg-card p-8 text-center shadow-card">
                <img src="{{ $img }}/asesmen-kosong.webp" width="700" height="700" loading="lazy" alt=""
                     class="size-44 object-contain">
                <p class="mt-4 text-lg font-medium text-text">Daftar asesmen sedang disiapkan.</p>
                <div class="mt-6"><x-button-link href="{{ url('/') }}">Kembali ke Beranda</x-button-link></div>
            </div>
        @else
            <ul data-stagger class="grid gap-4 md:grid-cols-2 lg:gap-6">
                @foreach ($assessments as $item)
                    <li data-item data-lift class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                        <span class="grid size-14 place-items-center rounded-full {{ $accents[$loop->index % 3]['circle'] }}" aria-hidden="true">
                            <x-icon name="clipboard-check" class="size-7" />
                        </span>

                        <h2 class="mt-4 text-2xl font-semibold">
                            <a href="{{ url('/asesmen/'.$item['slug']) }}" class="text-text underline-offset-4 hover:text-brand-strong hover:underline">{{ $item['name'] }}</a>
                        </h2>

                        @if ($item['is_demo'])
                            <p class="mt-1 text-sm text-warning">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
                        @endif

                        <p class="mt-2 text-text-secondary">{{ $item['summary'] }}</p>

                        <p class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-text-secondary">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="clock" class="size-4 text-brand-strong" />
                                Sekitar {{ $item['estimated_minutes'] }} menit
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="list-checks" class="size-4 text-brand-strong" />
                                {{ $item['question_count'] }} pertanyaan
                            </span>
                        </p>

                        @if (count($item['sub_scales']) > 0)
                            <ul class="mt-4 flex flex-wrap gap-2" aria-label="Aspek yang diukur">
                                @foreach ($item['sub_scales'] as $subScale)
                                    <li class="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text">{{ $subScale }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-auto pt-6">
                            <x-button-link href="{{ url('/asesmen/'.$item['slug']) }}" variant="outline" aria-label="Lihat detail {{ $item['name'] }}">Lihat detail</x-button-link>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @guest
            <x-disclaimer data-reveal class="mt-8 flex items-start gap-3">
                <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-brand-strong" />
                <p>Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.</p>
            </x-disclaimer>
        @endguest
    </section>

    {{-- Disclaimer --}}
    <section class="bg-neutral-soft" aria-label="Catatan penting">
        <div data-reveal class="mx-auto max-w-3xl px-4 py-12 text-center">
            <x-disclaimer class="text-left">
                Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
            </x-disclaimer>
            <p class="mt-4">
                <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Bicara dengan profesional</a>
            </p>
        </div>
    </section>

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
