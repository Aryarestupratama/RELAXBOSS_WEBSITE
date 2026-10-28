@extends('layouts.public')

@php
    // Halaman hasil pencarian tidak diindeks dan berkanonik ke daftar biasa (hindari konten tipis ganda).
    $searching = $searchTerm !== '';
    $canonical = url('/artikel').'?'.http_build_query(array_filter([
        'kategori' => $activeCategory,
        'page' => ! $searching && $articles->currentPage() > 1 ? $articles->currentPage() : null,
    ]));
    $canonical = rtrim($canonical, '?');
    $title = $activeCategoryLabel ? 'Artikel '.$activeCategoryLabel.' | RelaxBoss' : 'Artikel | RelaxBoss';
    $pill = 'inline-flex min-h-11 items-center rounded-full border border-border/60 bg-card px-4 text-sm font-medium text-text transition-colors hover:bg-soft-lavender aria-[current=page]:border-primary aria-[current=page]:bg-primary aria-[current=page]:text-on-primary';
    // Pil kategori menjaga kata kunci yang sedang dicari; "Hapus pencarian" menjaga kategori.
    $withTerm = function (?string $kategori) use ($searchTerm): string {
        $query = http_build_query(array_filter(['kategori' => $kategori, 'q' => $searchTerm]));

        return url('/artikel').($query !== '' ? '?'.$query : '');
    };
    $clearUrl = $activeCategory ? url('/artikel').'?kategori='.$activeCategory : url('/artikel');
    $emptyTitle = $searching
        ? 'Belum ada artikel yang cocok dengan “'.$searchTerm.'”.'
        : ($activeCategory ? 'Belum ada artikel untuk kategori ini.' : 'Artikel sedang disiapkan.');
@endphp

@section('title', $title)
@section('description', 'Bacaan singkat seputar kesehatan mental untuk mahasiswa. Ditulis dengan bahasa sederhana, bukan diagnosis dan bukan pengganti konsultasi profesional.')
@section('canonical', e($canonical))

@if ($searching)
    @push('head')
        <meta name="robots" content="noindex, follow">
    @endpush
@endif

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

            {{-- Pencarian judul dan kategori (GET, tanpa JS). Kategori yang aktif ikut terkirim. --}}
            <form data-load method="GET" action="{{ url('/artikel') }}" role="search" class="mx-auto mt-6 flex max-w-xl items-center gap-2">
                @if ($activeCategory)
                    <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                @endif
                <div class="relative min-w-0 flex-1">
                    <label for="cari-artikel" class="sr-only">Cari artikel</label>
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-text-secondary" />
                    <input id="cari-artikel" type="search" name="q" value="{{ $searchTerm }}"
                           maxlength="{{ config('relaxboss.limits.articles_search_max_chars') }}" autocomplete="off"
                           placeholder="Cari judul atau kategori"
                           class="min-h-11 w-full rounded-full border-[1.5px] border-input bg-card pr-4 pl-11 text-base text-text shadow-card placeholder:text-text-muted">
                </div>
                <x-ui.button type="submit" class="rounded-full! px-5 text-sm">Cari</x-ui.button>
            </form>

            @if ($categories->isNotEmpty())
                <nav data-load aria-label="Kategori artikel" class="mt-4">
                    <ul class="flex flex-wrap justify-center gap-2">
                        <li>
                            <a href="{{ $withTerm(null) }}" @if ($activeCategory === null) aria-current="page" @endif class="{{ $pill }}">Semua</a>
                        </li>
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ $withTerm($category['value']) }}" @if ($activeCategory === $category['value']) aria-current="page" @endif class="{{ $pill }}">{{ $category['label'] }}</a>
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
                <div data-reveal="pop" class="mx-auto max-w-xl rounded-3xl bg-card shadow-lift px-5 py-8">
                    <x-empty-state icon="newspaper" :title="$emptyTitle">
                        @if ($searching)
                            <x-button-link href="{{ $clearUrl }}" class="rounded-full! px-6 text-sm">Hapus pencarian</x-button-link>
                        @elseif ($activeCategory)
                            <x-button-link href="{{ url('/artikel') }}" class="rounded-full! px-6 text-sm">Lihat semua artikel</x-button-link>
                        @else
                            <x-button-link href="{{ url('/') }}" class="rounded-full! px-6 text-sm">Kembali ke Beranda</x-button-link>
                        @endif
                    </x-empty-state>
                </div>
            @else
                @if ($searching)
                    <p role="status" class="mb-5 flex flex-wrap items-center justify-between gap-x-4 text-sm text-text-secondary">
                        <span>{{ $articles->total() }} artikel untuk “{{ $searchTerm }}”@if ($activeCategoryLabel) di kategori {{ $activeCategoryLabel }}@endif</span>
                        <a href="{{ $clearUrl }}" class="inline-flex min-h-11 items-center underline underline-offset-4 transition-colors hover:text-primary">Hapus pencarian</a>
                    </p>
                @endif

                <ul data-stagger class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($articles as $item)
                        <x-article-card :item="$item" />
                    @endforeach
                </ul>

                <x-ui.pagination :paginator="$articles" label="Halaman artikel" class="mt-8" />
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
