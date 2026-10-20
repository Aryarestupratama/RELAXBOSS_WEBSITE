@extends('layouts.public')

@php
    $canonical = url('/artikel').'?'.http_build_query(array_filter([
        'kategori' => $activeCategory,
        'page' => $articles->currentPage() > 1 ? $articles->currentPage() : null,
    ]));
    $canonical = rtrim($canonical, '?');
    $title = $activeCategoryLabel ? 'Artikel '.$activeCategoryLabel.' | RelaxBoss' : 'Artikel | RelaxBoss';
@endphp

@section('title', $title)
@section('description', 'Bacaan singkat seputar kesehatan mental untuk mahasiswa. Ditulis dengan bahasa sederhana, bukan diagnosis dan bukan pengganti konsultasi profesional.')
@section('canonical', e($canonical))

@section('content')
    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto max-w-6xl px-4 py-12 lg:py-16">
            <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">
                {{ $activeCategoryLabel ? 'Artikel: '.$activeCategoryLabel : 'Artikel' }}
            </h1>
            <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">
                Bacaan singkat seputar kesehatan mental untuk mahasiswa, dengan bahasa yang mudah dipahami.
            </p>

            @if ($categories->isNotEmpty())
                <nav data-load aria-label="Kategori artikel" class="mt-8">
                    <ul class="flex flex-wrap gap-2">
                        <li>
                            <a href="{{ url('/artikel') }}" @if ($activeCategory === null) aria-current="page" @endif
                               class="inline-flex min-h-11 items-center rounded-full border border-border bg-card px-4 text-text transition-colors hover:bg-brand-soft aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-on-primary">Semua</a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ url('/artikel').'?kategori='.$category['value'] }}" @if ($activeCategory === $category['value']) aria-current="page" @endif
                                   class="inline-flex min-h-11 items-center rounded-full border border-border bg-card px-4 text-text transition-colors hover:bg-brand-soft aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-on-primary">{{ $category['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </section>

    {{-- Daftar artikel --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-label="Daftar artikel">
        @if ($articles->isEmpty())
            <div data-reveal="pop" class="mx-auto flex max-w-xl flex-col items-center rounded-xl border border-border bg-card p-8 text-center shadow-card">
                <img src="{{ asset('images/landing/robot-wave.webp') }}" width="800" height="800" loading="lazy" alt="" class="size-44 object-contain">
                <p class="mt-4 text-lg font-medium text-text">
                    {{ $activeCategory ? 'Belum ada artikel untuk kategori ini.' : 'Artikel sedang disiapkan.' }}
                </p>
                <div class="mt-6">
                    @if ($activeCategory)
                        <x-button-link href="{{ url('/artikel') }}">Lihat semua artikel</x-button-link>
                    @else
                        <x-button-link href="{{ url('/') }}">Kembali ke Beranda</x-button-link>
                    @endif
                </div>
            </div>
        @else
            <ul data-stagger class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                @foreach ($articles as $item)
                    <x-article-card :item="$item" :index="$loop->index" />
                @endforeach
            </ul>

            @if ($articles->hasPages())
                <nav aria-label="Halaman artikel" class="mt-10 flex items-center justify-between gap-4">
                    @if ($articles->previousPageUrl())
                        <x-button-link href="{{ $articles->previousPageUrl() }}" variant="outline">Sebelumnya</x-button-link>
                    @else
                        <span></span>
                    @endif
                    @if ($articles->nextPageUrl())
                        <x-button-link href="{{ $articles->nextPageUrl() }}" variant="outline">Berikutnya</x-button-link>
                    @endif
                </nav>
            @endif
        @endif
    </section>

    {{-- Disclaimer --}}
    <section class="bg-neutral-soft" aria-label="Catatan penting">
        <div data-reveal class="mx-auto max-w-3xl px-4 py-12 text-center">
            <x-disclaimer class="text-left">
                Artikel ini bersifat edukasi, bukan diagnosis dan bukan pengganti konsultasi dengan psikolog atau tenaga profesional.
            </x-disclaimer>
            <p class="mt-4">
                <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Bicara dengan profesional</a>
            </p>
        </div>
    </section>

    {{-- Bantuan --}}
    <section class="mx-auto max-w-6xl px-4 py-12" aria-label="Bantuan">
        <x-help-box data-reveal />
    </section>
@endsection
