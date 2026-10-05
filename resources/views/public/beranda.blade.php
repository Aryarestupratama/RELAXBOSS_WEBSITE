@extends('layouts.public')

@section('title', 'RelaxBoss: Asesmen, Mood Tracker, dan RelaxMate')
@section('description', 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.')

@section('content')
    {{-- Hero --}}
    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 py-12 md:grid-cols-2 md:py-20">
        <div>
            <h1>Kita yang mengendalikan stres, bukan sebaliknya.</h1>
            <p class="mt-4 max-w-prose text-text-secondary">
                Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja. Gratis, langsung dari browser.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
                <x-button-link href="{{ url('/asesmen') }}" variant="outline">Lihat asesmen</x-button-link>
            </div>
        </div>

        {{-- Gelembung emosi: hiasan murni --}}
        <div class="relative mx-auto h-72 w-72 sm:h-80 sm:w-80" aria-hidden="true">
            <div class="absolute left-1/2 top-1/2 size-60 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-dotted border-brand-strong/45 sm:size-64"></div>
            <div class="absolute left-1/2 top-1/2 flex size-28 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-brand text-base font-semibold text-on-primary">Tenang</div>
            <div class="absolute left-4 top-4 flex size-20 items-center justify-center rounded-full bg-mood-blue text-sm font-medium text-text">Sedih</div>
            <div class="absolute right-2 top-8 flex size-[4.5rem] items-center justify-center rounded-full bg-mood-green text-sm font-medium text-text">Lega</div>
            <div class="absolute bottom-8 left-0 flex size-[4.5rem] items-center justify-center rounded-full bg-mood-red text-sm font-medium text-text">Stres</div>
            <div class="absolute bottom-2 right-8 flex size-20 items-center justify-center rounded-full bg-mood-yellow text-sm font-medium text-text">Cemas</div>
        </div>
    </section>

    {{-- Tiga fitur --}}
    <section class="mx-auto max-w-6xl px-4 pb-12" aria-labelledby="judul-fitur">
        <h2 id="judul-fitur" class="sr-only">Fitur RelaxBoss</h2>
        <div class="grid gap-4 lg:grid-cols-3">
            <article class="rounded-xl bg-mood-yellow p-5 text-text shadow-card lg:p-6">
                <span class="flex size-14 items-center justify-center rounded-full bg-card text-brand-strong"><x-icon name="clipboard-check" class="size-7" /></span>
                <h3 class="mt-4">Asesmen</h3>
                <p class="mt-2">Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu.</p>
            </article>
            <article class="rounded-xl bg-mood-green p-5 text-text shadow-card lg:p-6">
                <span class="flex size-14 items-center justify-center rounded-full bg-card text-brand-strong"><x-icon name="smile" class="size-7" /></span>
                <h3 class="mt-4">Mood Tracker</h3>
                <p class="mt-2">Catat perasaanmu dan lihat polanya dalam seminggu.</p>
            </article>
            <article class="rounded-xl bg-brand p-5 text-on-primary shadow-card lg:p-6">
                <span class="flex size-14 items-center justify-center rounded-full bg-card text-brand-strong"><x-icon name="message-circle" class="size-7" /></span>
                <h3 class="mt-4">RelaxMate</h3>
                <p class="mt-2">Teman bicara berbasis AI. Bukan psikolog atau layanan medis.</p>
            </article>
        </div>
    </section>

    {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
    @if (filled($umb))
        <section class="mx-auto max-w-3xl px-4 pb-12" aria-labelledby="judul-umb">
            <h2 id="judul-umb">Kaitan akademik</h2>
            <p class="mt-3 text-text-secondary">{{ $umb }}</p>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 pb-16">
        <x-disclaimer>
            RelaxBoss bukan psikolog atau layanan medis. Untuk penilaian yang tepat, bicarakan dengan tenaga profesional.
            <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Lihat jalur konsultasi</a>.
        </x-disclaimer>
    </section>
@endsection
