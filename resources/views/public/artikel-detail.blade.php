@extends('layouts.public')

@section('title', $article['seo_title'])
@section('description', $article['seo_description'])
@section('og_type', 'article')

@push('head')
    <meta property="article:published_time" content="{{ $article['published_atom'] }}">
    <meta property="article:modified_time" content="{{ $article['updated_atom'] }}">
    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $article['json_ld'] !!}</script>
@endpush

@section('content')
    {{-- 1. Kepala: jejak halaman, kategori, judul, ringkasan, meta --}}
    <section class="bg-background">
        <div class="mx-auto max-w-3xl px-4 pt-6 pb-8 md:pt-8 md:pb-10">
            <nav aria-label="Jejak halaman">
                <ol class="flex flex-wrap items-center gap-x-2 text-sm text-text-secondary">
                    <li><a href="{{ url('/') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-text hover:underline">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ url('/artikel') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-text hover:underline">Artikel</a></li>
                </ol>
            </nav>

            <p data-load class="mt-2">
                <a href="{{ url('/artikel').'?kategori='.$article['category'] }}" class="inline-flex min-h-11 items-center rounded-full bg-soft-lavender px-4 text-sm font-medium text-text transition-shadow hover:shadow-lift">{{ $article['category_label'] }}</a>
            </p>

            <h1 data-load class="mt-2 text-[1.75rem] leading-[1.15] font-bold tracking-tight md:text-4xl lg:text-[2.75rem]">{{ $article['title'] }}</h1>

            @if ($article['is_demo'])
                <p data-load class="mt-3 w-fit rounded-full bg-soft-peach px-3 py-1 text-xs font-medium text-text">Artikel contoh untuk pengembangan, bukan konten yang sebenarnya.</p>
            @endif

            <p data-load class="mt-4 max-w-prose text-base text-text-secondary md:text-lg">{{ $article['excerpt'] }}</p>

            <ul data-load class="mt-5 flex flex-wrap items-center gap-2 text-xs text-text" aria-label="Info artikel">
                <li class="inline-flex items-center gap-1.5 rounded-full bg-surface-alt px-3 py-1.5"><x-icon name="user" class="size-4 text-primary" />{{ $article['author'] }}</li>
                <li class="rounded-full bg-surface-alt px-3 py-1.5">Terbit <time datetime="{{ $article['published_iso'] }}">{{ $article['published_label'] }}</time></li>
                @if ($article['updated_label'])
                    <li class="rounded-full bg-surface-alt px-3 py-1.5">Diperbarui {{ $article['updated_label'] }}</li>
                @endif
                <li class="inline-flex items-center gap-1.5 rounded-full bg-surface-alt px-3 py-1.5"><x-icon name="clock" class="size-4 text-primary" />{{ $article['reading_minutes'] }} menit baca</li>
            </ul>
        </div>
    </section>

    {{-- 2. Isi --}}
    <section class="bg-background">
        <article class="mx-auto max-w-3xl px-4 pb-10 md:pb-14">
            <x-article-prose :html="$article['html']" />
        </article>
    </section>

    {{-- 3. Asesmen terkait --}}
    @if ($assessment)
        <section class="bg-background" aria-label="Asesmen terkait">
            <div class="mx-auto max-w-3xl px-4 pb-10 md:pb-14">
                <div data-reveal class="flex flex-col gap-5 rounded-3xl bg-card p-6 text-text shadow-lift sm:flex-row sm:items-center md:p-8">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="clipboard-check" class="size-5" /></span>
                    <div class="flex-1">
                        <h2 class="text-xl font-bold">Ingin mengenali kondisimu?</h2>
                        <p class="mt-1 text-sm leading-relaxed text-text-secondary">Coba asesmen {{ $assessment['name'] }} untuk gambaran awal. Hasilnya bukan diagnosis.</p>
                    </div>
                    <x-button-link href="{{ url('/asesmen/'.$assessment['slug']) }}" class="rounded-full! px-6 text-sm">Lihat asesmen</x-button-link>
                </div>
            </div>
        </section>
    @endif


    {{-- Catatan: x-inline-note (satu kalimat, selebar konten) --}}
    <section class="bg-background" aria-label="Catatan penting">
        <div class="mx-auto max-w-3xl px-4 pb-10 md:pb-14">
            <x-inline-note data-reveal>Artikel ini bersifat edukasi, bukan diagnosis atau pengganti <a href="{{ url('/konsultasi') }}">konsultasi profesional</a>.</x-inline-note>
        </div>
    </section>

    {{-- 5. Artikel lainnya --}}
    @if ($related->isNotEmpty())
        <section class="bg-surface-alt" aria-labelledby="artikel-lain">
            <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
                <h2 id="artikel-lain" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Artikel lainnya</h2>
                <ul data-stagger class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-article-card :item="$item" />
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
