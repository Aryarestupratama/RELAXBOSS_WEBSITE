@extends('layouts.public')

@section('title', 'Konsultasi Profesional | RelaxBoss')
@section('description', 'Kapan sebaiknya mencari bantuan profesional untuk stres, dan daftar kontak bantuan yang bisa kamu hubungi.')

@section('content')
    @php
        // Pastel yang sama dengan Beranda dan Asesmen (Design 2); teks di atasnya --text.
        $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
        // Butir ini bukan urutan langkah, jadi tanpa nomor dan tanpa ikon (bento di bagian 3).
        $signs = [
            'Rasa tertekan berlangsung berminggu-minggu dan mengganggu kuliah, tidur, atau makanmu.',
            'Kamu sulit menikmati hal yang dulu kamu sukai.',
            'Kamu merasa kewalahan terus-menerus dan sulit mengatasinya sendiri.',
            'Orang terdekat mulai mengkhawatirkan keadaanmu.',
        ];
    @endphp

    {{-- 1. Judul di tengah (pola Asesmen dan Artikel), tanpa ilustrasi dan tombol --}}
    <section class="bg-background" aria-labelledby="judul-konsultasi">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-6 text-center md:pt-14 md:pb-8">
            <h1 id="judul-konsultasi" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">Konsultasi profesional</h1>
            <p data-load class="mx-auto mt-4 max-w-xl text-base text-text-secondary">
                RelaxBoss tidak menggantikan psikolog atau tenaga kesehatan jiwa.
                Bicara dengan profesional itu wajar, tanpa perlu menunggu keadaan parah.
            </p>
        </div>
    </section>

    {{-- 2. Kotak darurat: tetap tampil karena ini inti halaman (warna krisis dari token) --}}
    <section class="bg-background" aria-labelledby="darurat">
        <div class="mx-auto max-w-6xl px-4 pb-10 md:pb-14">
            <div data-reveal class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-crisis-text/30 bg-crisis-bg p-5 text-crisis-text md:flex-row md:items-center md:p-6">
                <div>
                    <h2 id="darurat" class="text-lg font-bold md:text-xl">Kalau kamu sedang dalam bahaya</h2>
                    <p class="mt-1.5 max-w-2xl text-sm leading-relaxed">Kalau kamu berpikir menyakiti dirimu sendiri, hubungi bantuan sekarang atau minta seseorang menemanimu.</p>
                </div>
                <a href="#kontak-bantuan" class="inline-flex min-h-11 shrink-0 items-center text-sm font-semibold underline underline-offset-4">Lihat kontak bantuan</a>
            </div>
        </div>
    </section>

    {{-- 3. Kapan sebaiknya mencari bantuan --}}
    <section class="bg-surface-alt" aria-labelledby="kapan">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <h2 id="kapan" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Kapan sebaiknya mencari bantuan</h2>
            {{-- Bento: kartu pertama besar (dua kolom, dua baris), kartu krisis selebar penuh di bawah. --}}
            <ul data-stagger class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($signs as $sign)
                    @php
                        $cell = [
                            'lg:col-span-2 lg:row-span-2',
                            'lg:col-span-2',
                            'lg:col-span-1',
                            'lg:col-span-1',
                        ][$loop->index];
                        $big = $loop->first;
                    @endphp
                    <li data-item class="{{ $cell }}">
                        <x-ui.card data-lift class="h-full justify-between {{ $big ? 'bg-soft-lavender!' : '' }} transition-shadow duration-300 hover:shadow-lift-hover">
                            <x-ui.card.content class="flex h-full flex-col gap-6">
                                <span class="h-1 w-10 rounded-full bg-primary/30" aria-hidden="true"></span>
                                <p class="{{ $big ? 'mt-auto text-xl leading-snug font-semibold md:text-2xl' : 'text-sm leading-relaxed md:text-base' }} text-text">{{ $sign }}</p>
                            </x-ui.card.content>
                        </x-ui.card>
                    </li>
                @endforeach
                <li data-item class="sm:col-span-2 lg:col-span-4">
                    <x-ui.card class="border-crisis-text/30 bg-crisis-bg! text-crisis-text">
                        <x-ui.card.content>
                            <p class="text-sm font-semibold md:text-base">Kamu punya pikiran untuk menyakiti diri sendiri.</p>
                        </x-ui.card.content>
                    </x-ui.card>
                </li>
            </ul>
        </div>
    </section>

    {{-- 4. Kontak bantuan (hanya dari config, OQ-3), ditutup catatan sebaris --}}
    <section id="kontak-bantuan" class="scroll-mt-24 bg-background" aria-labelledby="kontak">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-14 md:pt-14 md:pb-20">
            <h2 id="kontak" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Kontak bantuan</h2>

            @if (count($contacts) === 0)
                <div data-reveal="pop" class="mx-auto mt-6 flex max-w-xl flex-col items-center rounded-3xl bg-card shadow-lift px-5 py-10 text-center">
                    <span class="grid size-16 place-items-center rounded-full bg-card text-primary" aria-hidden="true"><x-icon name="phone" class="size-7" /></span>
                    <p class="mt-4 text-lg font-semibold text-text">Daftar kontak sedang disiapkan.</p>
                </div>
            @else
                <ul data-stagger class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($contacts as $contact)
                        @php
                            $phone = $contact['phone'] ?? null;
                            $link = $contact['url'] ?? null;
                            $isDummy = (bool) ($contact['dummy'] ?? false);
                        @endphp
                        <li data-item>
                            <x-ui.card class="h-full transition-shadow duration-300 hover:shadow-lift-hover" data-lift>
                                <x-ui.card.header>
                                    <span class="mb-1 grid size-11 place-items-center rounded-full text-primary {{ $tones[$loop->index % 4] }}" aria-hidden="true">
                                        <x-icon name="phone" class="size-5" />
                                    </span>
                                    {{-- Tiga zona tetap agar semua kartu sejajar: nama (tinggi dua baris), kontak, catatan di dasar --}}
                                    <x-ui.card.title class="sm:min-h-[3.2rem]">{{ $contact['name'] ?? '' }}</x-ui.card.title>
                                </x-ui.card.header>

                                <x-ui.card.content class="flex flex-col">
                                    @if (filled($phone))
                                        <a href="tel:{{ preg_replace('/[^0-9+,]/', '', preg_replace('/\s*(?:ext\.?|ekstensi)\s*(\d+)/i', ',$1', $phone)) }}" class="inline-flex min-h-11 items-center font-semibold text-primary underline underline-offset-4">{{ $phone }}</a>
                                    @endif
                                    @if (is_string($link) && str_starts_with($link, 'https://'))
                                        <a href="{{ $link }}" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-sm font-medium break-all text-primary underline underline-offset-4">{{ $link }}</a>
                                    @endif
                                </x-ui.card.content>

                                @if ($isDummy || filled($contact['note'] ?? null))
                                    <x-ui.card.footer class="flex-col gap-4">
                                        <x-ui.separator />
                                        @if ($isDummy)
                                            <p><x-ui.badge variant="soft" tone="peach">Contoh sementara, bukan kontak yang sebenarnya.</x-ui.badge></p>
                                        @else
                                            <p class="text-sm text-text-secondary">{{ $contact['note'] }}</p>
                                        @endif
                                    </x-ui.card.footer>
                                @endif
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>
            @endif

            <x-inline-note data-reveal class="mt-8">RelaxBoss bukan psikolog atau layanan medis, dan hasil Asesmen bukan diagnosis.</x-inline-note>
        </div>
    </section>
@endsection
