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
    {{-- Kepala --}}
    <section class="bg-neutral-soft">
        <div class="mx-auto max-w-3xl px-4 py-10 lg:py-14">
            <nav aria-label="Jejak halaman">
                <ol class="flex flex-wrap items-center gap-x-2 text-sm text-text-secondary">
                    <li><a href="{{ url('/') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-text hover:underline">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ url('/artikel') }}" class="inline-flex min-h-11 items-center underline-offset-4 hover:text-text hover:underline">Artikel</a></li>
                </ol>
            </nav>

            <p data-load class="mt-2">
                <a href="{{ url('/artikel').'?kategori='.$article['category'] }}" class="inline-flex min-h-11 items-center rounded-full bg-brand-soft px-4 text-sm font-medium text-text">{{ $article['category_label'] }}</a>
            </p>

            <h1 data-load class="mt-2 text-[1.75rem] leading-[1.2] font-semibold md:text-4xl md:leading-[1.2]">{{ $article['title'] }}</h1>

            @if ($article['is_demo'])
                <p data-load class="mt-3 text-sm text-warning">Artikel contoh untuk pengembangan, bukan konten yang sebenarnya.</p>
            @endif

            <p data-load class="mt-4 text-lg text-text-secondary">{{ $article['excerpt'] }}</p>

            <p data-load class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-text-secondary">
                <span class="inline-flex items-center gap-1.5"><x-icon name="user" class="size-4 text-brand-strong" />{{ $article['author'] }}</span>
                <span>Terbit <time datetime="{{ $article['published_iso'] }}">{{ $article['published_label'] }}</time></span>
                @if ($article['updated_label'])
                    <span>Diperbarui {{ $article['updated_label'] }}</span>
                @endif
                <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-brand-strong" />{{ $article['reading_minutes'] }} menit baca</span>
            </p>
        </div>
    </section>

    {{-- Isi --}}
    <article class="mx-auto max-w-3xl px-4 py-10 md:py-14">
        <x-article-prose :html="$article['html']" />
    </article>

    {{-- Asesmen terkait --}}
    @if ($assessment)
        <section class="mx-auto max-w-3xl px-4 pb-10" aria-label="Asesmen terkait">
            <div data-reveal class="rounded-xl bg-brand-soft p-6 text-text">
                <h2 class="text-xl font-semibold">Ingin mengenali kondisimu?</h2>
                <p class="mt-2">Coba asesmen {{ $assessment['name'] }} untuk gambaran awal. Hasilnya bukan diagnosis.</p>
                <div class="mt-4"><x-button-link href="{{ url('/asesmen/'.$assessment['slug']) }}">Lihat asesmen</x-button-link></div>
            </div>
        </section>
    @endif

    {{-- Disclaimer --}}
    <section class="bg-neutral-soft" aria-label="Catatan penting">
        <div data-reveal class="mx-auto max-w-3xl px-4 py-10">
            <x-disclaimer>
                Artikel ini bersifat edukasi, bukan diagnosis dan bukan pengganti konsultasi dengan psikolog atau tenaga profesional.
            </x-disclaimer>
            <p class="mt-4">
                <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Bicara dengan profesional</a>
            </p>
        </div>
    </section>

    {{-- Artikel lainnya --}}
    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="artikel-lain">
            <h2 id="artikel-lain" class="text-2xl font-semibold">Artikel lainnya</h2>
            <ul data-stagger class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                @foreach ($related as $item)
                    <x-article-card :item="$item" :index="$loop->index" />
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Bantuan --}}
    <section class="mx-auto max-w-6xl px-4 pb-12" aria-label="Bantuan">
        <x-help-box data-reveal />
    </section>
@endsection
