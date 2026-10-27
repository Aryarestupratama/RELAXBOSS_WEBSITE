@extends('layouts.public')

@section('title', 'Daftar Asesmen | RelaxBoss')
@section('description', 'Pilih asesmen untuk mengenali kondisimu: stres, kecemasan, dan kelelahan. Gratis, hasilnya bisa kamu lihat lagi kapan saja.')

@section('content')
    @php
        // Hanya lingkaran ikon yang berwarna (pastel Beranda); kartu sendiri putih.
        $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
    @endphp

    {{-- 1. Judul: di tengah, tanpa tombol (Masuk dan Daftar sudah ada di navbar) --}}
    <section class="bg-background" aria-labelledby="judul-asesmen">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-8 text-center md:pt-14 md:pb-10">
            <h1 id="judul-asesmen" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">Asesmen</h1>
            <p data-load class="mx-auto mt-4 max-w-3xl text-base text-text-secondary">
                <span class="block">Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu.</span>
                <span class="block">Pilih yang ingin kamu coba, lalu masuk atau daftar gratis untuk mengerjakannya.</span>
            </p>

            {{-- Validasi: tepat di bawah deskripsi; hanya tampil bila config terisi (RULE-049) --}}
            @if (count($validation) > 0)
                <ul data-load class="mx-auto mt-8 grid max-w-3xl gap-4 text-left sm:grid-cols-2" aria-label="Validasi asesmen">
                    @foreach ($validation as $item)
                        <li>
                            <x-ui.card :tone="['lavender', 'sky', 'mint', 'peach'][$loop->index % 4]" class="h-full">
                                <x-ui.card.content class="flex items-start gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
                                        <x-icon :name="$item['icon']" class="size-5" />
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold">{{ $item['title'] }}</p>
                                        <p class="mt-1 text-sm leading-relaxed text-text-secondary">{{ $item['text'] }}</p>
                                    </div>
                                </x-ui.card.content>
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- 2. Kartu asesmen (putih, menonjol lewat bayangan) dan catatan di bawahnya --}}
    <section class="bg-background" aria-labelledby="judul-daftar-asesmen">
        <div class="mx-auto max-w-6xl px-4 pb-14 md:pb-20">
            <h2 id="judul-daftar-asesmen" data-reveal class="mb-6 text-center text-2xl leading-tight font-bold tracking-tight md:mb-8 md:text-[2rem]">Daftar asesmen kami</h2>

            @if ($assessments->isEmpty())
                <div data-reveal="pop" class="mx-auto max-w-xl rounded-3xl bg-card shadow-lift px-5 py-8">
                    <x-empty-state icon="clipboard-list" title="Daftar asesmen sedang disiapkan.">
                        <x-button-link href="{{ url('/') }}" class="rounded-full! px-6 text-sm">Kembali ke Beranda</x-button-link>
                    </x-empty-state>
                </div>
            @else
                <ul data-stagger class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($assessments as $item)
                        <li data-item>
                            <x-ui.card tag="article" :interactive="true" class="h-full">
                                <x-ui.card.header>
                                    <span class="mb-1 grid size-11 place-items-center rounded-full text-primary {{ $tones[$loop->index % 4] }}" aria-hidden="true">
                                        <x-icon name="clipboard-check" class="size-5" />
                                    </span>
                                    <x-ui.card.title tag="h2">
                                        {{-- Satu tautan; after:inset-0 membuat seluruh kartu bisa diklik --}}
                                        <a href="{{ url('/asesmen/'.$item['slug']) }}" class="text-text after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none">{{ $item['name'] }}</a>
                                    </x-ui.card.title>
                                    <x-ui.card.description>{{ $item['summary'] }}</x-ui.card.description>
                                </x-ui.card.header>

                                <x-ui.card.content class="space-y-3">
                                    <p class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-text-secondary">
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon name="clock" class="size-4 text-primary" />
                                            Sekitar {{ $item['estimated_minutes'] }} menit
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon name="list-checks" class="size-4 text-primary" />
                                            {{ $item['question_count'] }} pertanyaan
                                        </span>
                                    </p>

                                    @if (count($item['sub_scales']) > 0)
                                        <ul class="flex flex-wrap gap-1.5" aria-label="Aspek yang diukur">
                                            @foreach ($item['sub_scales'] as $subScale)
                                                <li><x-ui.badge>{{ $subScale }}</x-ui.badge></li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($item['is_demo'])
                                        <x-ui.alert variant="info" :compact="true">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</x-ui.alert>
                                    @endif
                                </x-ui.card.content>

                                {{-- Ajakan di dasar kartu: hiasan, tautan ada di judul --}}
                                <x-ui.card.footer class="flex-col gap-4">
                                    <x-ui.separator />
                                    <span class="flex items-center justify-between text-sm font-semibold text-primary" aria-hidden="true">
                                        Lihat detail
                                        <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                                    </span>
                                </x-ui.card.footer>
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- 3. Catatan --}}
            <div class="mt-8 space-y-3">
                @guest
                    <x-inline-note data-reveal>Masuk dulu untuk mengerjakan asesmen; hasilnya tersimpan di riwayatmu.</x-inline-note>
                @endguest
                <x-inline-note data-reveal>Hasil ini bukan diagnosis; untuk penilaian yang tepat, <a href="{{ url('/konsultasi') }}">bicarakan dengan profesional</a>.</x-inline-note>
            </div>
        </div>
    </section>
@endsection
