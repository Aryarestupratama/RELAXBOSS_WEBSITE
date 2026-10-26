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
            <p data-load class="mx-auto mt-4 max-w-xl text-base text-text-secondary">
                Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Pilih asesmen yang ingin kamu coba,
                lalu masuk atau daftar gratis untuk mengerjakannya.
            </p>
        </div>
    </section>

    {{-- 2. Kartu asesmen (putih, menonjol lewat bayangan) dan catatan di bawahnya --}}
    <section class="bg-background" aria-label="Daftar asesmen">
        <div class="mx-auto max-w-6xl px-4 pb-14 md:pb-20">
            @if ($assessments->isEmpty())
                <div data-reveal="pop" class="mx-auto flex max-w-xl flex-col items-center rounded-3xl bg-card shadow-lift px-5 py-10 text-center">
                    <span class="grid size-16 place-items-center rounded-full bg-card text-primary" aria-hidden="true"><x-icon name="clipboard-check" class="size-7" /></span>
                    <p class="mt-4 text-lg font-semibold text-text">Daftar asesmen sedang disiapkan.</p>
                    <div class="mt-5"><x-button-link href="{{ url('/') }}" class="rounded-full! px-6 text-sm">Kembali ke Beranda</x-button-link></div>
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

            {{-- Validasi: hanya tampil bila config terisi (RULE-049) --}}
            @if (count($validation) > 0)
                <ul data-reveal class="mx-auto mt-10 grid max-w-3xl gap-4 sm:grid-cols-2" aria-label="Validasi asesmen">
                    @foreach ($validation as $item)
                        <li>
                            <x-ui.card :tone="['lavender', 'sky', 'mint', 'peach'][$loop->index % 4]" class="h-full">
                                <x-ui.card.content class="flex items-start gap-3">
                                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-card text-primary" aria-hidden="true">
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

            {{-- 3. Catatan --}}
            <x-ui.alert data-reveal icon="info" class="mx-auto mt-8 max-w-2xl">
                <div class="space-y-1.5 text-text-secondary">
                    @guest
                        <p>Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.</p>
                    @endguest
                    <p>
                        Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
                        <a href="{{ url('/konsultasi') }}" class="font-semibold text-primary underline underline-offset-4">Bicara dengan profesional</a>
                    </p>
                </div>
            </x-ui.alert>
        </div>
    </section>
@endsection
