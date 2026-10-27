@extends('layouts.public')

@php
    $canonical = url('/artikel').'?'.http_build_query(array_filter([
        'kategori' => $activeCategory,
        'page' => $articles->currentPage() > 1 ? $articles->currentPage() : null,
    ]));
    $canonical = rtrim($canonical, '?');
    $title = $activeCategoryLabel ? 'Artikel '.$activeCategoryLabel.' | RelaxBoss' : 'Artikel | RelaxBoss';
    $pill = 'inline-flex min-h-11 items-center rounded-full border border-border/60 bg-card px-4 text-sm font-medium text-text transition-colors hover:bg-soft-lavender aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-on-primary';
@endphp

@section('title', $title)
@section('description', 'Bacaan singkat seputar kesehatan mental untuk mahasiswa. Ditulis dengan bahasa sederhana, bukan diagnosis dan bukan pengganti konsultasi profesional.')
@section('canonical', e($canonical))

@section('content')
    {{-- 1. Judul di tengah (pola Asesmen) dan pil kategori --}}
    <section class="bg-background" aria-labelledby="judul-artikel">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-8 text-center md:pt-14 md:pb-10">
            <h1 id="judul-artikel" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">
                {{ $activeCategoryLabel ? 'Artikel: '.$activeCategoryLabel : 'Artikel' }}
            </h1>
            <p data-load class="mx-auto mt-4 max-w-xl text-base text-text-secondary">
                Bacaan singkat seputar kesehatan mental untuk mahasiswa, dengan bahasa yang mudah dipahami.
            </p>

            @if ($categories->isNotEmpty())
                <nav data-load aria-label="Kategori artikel" class="mt-6">
                    <ul class="flex flex-wrap justify-center gap-2">
                        <li>
                            <a href="{{ url('/artikel') }}" @if ($activeCategory === null) aria-current="page" @endif class="{{ $pill }}">Semua</a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ url('/artikel').'?kategori='.$category['value'] }}" @if ($activeCategory === $category['value']) aria-current="page" @endif class="{{ $pill }}">{{ $category['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    {{-- 2. Daftar artikel --}}
    <section class="bg-background" aria-label="Daftar artikel">
        <div class="mx-auto max-w-6xl px-4 pb-8 md:pb-10">
            @if ($articles->isEmpty())
                <div data-reveal="pop" class="mx-auto flex max-w-xl flex-col items-center rounded-3xl bg-card shadow-lift px-5 py-8 text-center">
                    <img src="{{ asset('images/landing/robot-wave.webp') }}" width="800" height="800" loading="lazy" alt="" class="size-32 object-contain">
                    <p class="mt-3 text-lg font-semibold text-text">
                        {{ $activeCategory ? 'Belum ada artikel untuk kategori ini.' : 'Artikel sedang disiapkan.' }}
                    </p>
                    <div class="mt-5">
                        @if ($activeCategory)
                            <x-button-link href="{{ url('/artikel') }}" class="rounded-full! px-6 text-sm">Lihat semua artikel</x-button-link>
                        @else
                            <x-button-link href="{{ url('/') }}" class="rounded-full! px-6 text-sm">Kembali ke Beranda</x-button-link>
                        @endif
                    </div>
                </div>
            @else
                <ul data-stagger class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $item)
                        <x-article-card :item="$item" />
                    @endforeach
                </ul>

                @if ($articles->hasPages())
                    <nav aria-label="Halaman artikel" class="mt-8 flex items-center justify-between gap-4">
                        @if ($articles->previousPageUrl())
                            <x-button-link href="{{ $articles->previousPageUrl() }}" variant="outline" class="rounded-full! border-2! bg-card px-5 text-sm">Sebelumnya</x-button-link>
                        @else
                            <span></span>
                        @endif
                        @if ($articles->nextPageUrl())
                            <x-button-link href="{{ $articles->nextPageUrl() }}" variant="outline" class="rounded-full! border-2! bg-card px-5 text-sm">Berikutnya</x-button-link>
                        @endif
                    </nav>
                @endif
            @endif
        </div>
    </section>

    {{-- Catatan: x-inline-note (satu kalimat, selebar konten) --}}
    <section class="bg-background" aria-label="Catatan penting">
        <div class="mx-auto max-w-6xl px-4 pb-14 md:pb-20">
            <x-inline-note data-reveal>Artikel ini bersifat edukasi, bukan diagnosis atau pengganti <a href="{{ url('/konsultasi') }}">konsultasi profesional</a>.</x-inline-note>
        </div>
    </section>
@endsection
