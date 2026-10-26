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
                <div data-reveal="pop" class="mx-auto flex max-w-xl flex-col items-center rounded-3xl bg-surface-alt px-5 py-10 text-center">
                    <span class="grid size-16 place-items-center rounded-full bg-card text-primary" aria-hidden="true"><x-icon name="clipboard-check" class="size-7" /></span>
                    <p class="mt-4 text-lg font-semibold text-text">Daftar asesmen sedang disiapkan.</p>
                    <div class="mt-5"><x-button-link href="{{ url('/') }}" class="rounded-full! px-6 text-sm">Kembali ke Beranda</x-button-link></div>
                </div>
            @else
                <ul data-stagger class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($assessments as $item)
                        <li data-item>
                            <article data-lift class="group relative flex h-full flex-col rounded-2xl border border-border/60 bg-card p-5 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring md:p-6">
                                <span class="grid size-11 place-items-center rounded-full text-primary {{ $tones[$loop->index % 4] }}" aria-hidden="true">
                                    <x-icon name="clipboard-check" class="size-5" />
                                </span>

                                <h2 class="mt-4 text-lg leading-snug font-bold">
                                    {{-- Satu tautan; after:inset-0 membuat seluruh kartu bisa diklik --}}
                                    <a href="{{ url('/asesmen/'.$item['slug']) }}" class="text-text after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none">{{ $item['name'] }}</a>
                                </h2>

                                <p class="mt-2 text-sm leading-relaxed text-text-secondary">{{ $item['summary'] }}</p>

                                <p class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-text-secondary">
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
                                    <ul class="mt-3 flex flex-wrap gap-1.5" aria-label="Aspek yang diukur">
                                        @foreach ($item['sub_scales'] as $subScale)
                                            <li class="rounded-full bg-surface-alt px-2.5 py-0.5 text-xs text-text">{{ $subScale }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($item['is_demo'])
                                    <p class="mt-4 rounded-lg bg-soft-peach px-3 py-2 text-xs leading-snug text-text">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
                                @endif

                                {{-- Ajakan di dasar kartu: hiasan, tautan ada di judul --}}
                                <div class="mt-auto pt-5">
                                    <span class="flex items-center justify-between border-t border-border/60 pt-4 text-sm font-semibold text-primary" aria-hidden="true">
                                        Lihat detail
                                        <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                                    </span>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Validasi: hanya tampil bila config terisi (RULE-049) --}}
            @if (count($validation) > 0)
                <ul data-reveal class="mx-auto mt-10 grid max-w-3xl gap-4 sm:grid-cols-2" aria-label="Validasi asesmen">
                    @foreach ($validation as $item)
                        <li class="flex items-start gap-3 rounded-2xl p-4 text-text {{ $tones[$loop->index % 4] }}">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-card text-primary" aria-hidden="true">
                                <x-icon :name="$item['icon']" class="size-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold">{{ $item['title'] }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-text-secondary">{{ $item['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- 3. Catatan: teks biasa dengan ikon "i", bukan kartu --}}
            <div data-reveal class="mx-auto mt-8 flex max-w-2xl items-start gap-2.5 text-sm leading-relaxed text-text-secondary" role="note">
                <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-primary" />
                <div class="space-y-1.5">
                    @guest
                        <p>Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.</p>
                    @endguest
                    <p>
                        Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
                        <a href="{{ url('/konsultasi') }}" class="font-semibold text-primary underline underline-offset-4">Bicara dengan profesional</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
