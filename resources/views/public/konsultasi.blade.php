@extends('layouts.public')

@section('title', 'Konsultasi Profesional | RelaxBoss')
@section('description', 'Kapan sebaiknya mencari bantuan profesional untuk stres, dan daftar kontak bantuan yang bisa kamu hubungi.')

@section('content')
    @php
        // Pastel yang sama dengan Beranda dan Asesmen (Design 2); teks di atasnya --text.
        $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
        // Butir ini bukan urutan langkah, jadi tanpa nomor.
        $signs = [
            ['icon' => 'moon', 'text' => 'Rasa tertekan berlangsung berminggu-minggu dan mengganggu kuliah, tidur, atau makanmu.'],
            ['icon' => 'smile', 'text' => 'Kamu sulit menikmati hal yang dulu kamu sukai.'],
            ['icon' => 'wind', 'text' => 'Kamu merasa kewalahan terus-menerus dan sulit mengatasinya sendiri.'],
            ['icon' => 'users', 'text' => 'Orang terdekat mulai mengkhawatirkan keadaanmu.'],
        ];
    @endphp

    {{-- 1. Judul di tengah (pola Asesmen dan Artikel), tanpa ilustrasi dan tombol --}}
    <section class="bg-background" aria-labelledby="judul-konsultasi">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-6 text-center md:pt-14 md:pb-8">
            <h1 id="judul-konsultasi" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">Konsultasi profesional</h1>
            <p data-load class="mx-auto mt-4 max-w-xl text-base text-text-secondary">
                RelaxBoss membantu mengenali kondisimu, tetapi tidak menggantikan psikolog atau tenaga kesehatan jiwa.
                Bicara dengan profesional adalah langkah yang wajar dan tidak perlu menunggu sampai keadaan parah.
            </p>
        </div>
    </section>

    {{-- 2. Kotak darurat: tetap tampil karena ini inti halaman (warna krisis dari token) --}}
    <section class="bg-background" aria-labelledby="darurat">
        <div class="mx-auto max-w-6xl px-4 pb-10 md:pb-14">
            <div data-reveal class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-crisis-text/30 bg-crisis-bg p-5 text-crisis-text md:flex-row md:items-center md:p-6">
                <div class="flex items-start gap-4">
                    <span class="mt-0.5 shrink-0" aria-hidden="true"><x-icon name="shield" class="size-6" /></span>
                    <div>
                        <h2 id="darurat" class="text-lg font-bold md:text-xl">Kalau kamu sedang dalam bahaya</h2>
                        <p class="mt-1.5 max-w-2xl text-sm leading-relaxed">
                            Kalau kamu berpikir untuk menyakiti dirimu sendiri, tolong hubungi bantuan sekarang atau minta seseorang yang kamu percaya menemanimu.
                            Kamu tidak harus menghadapi ini sendirian.
                        </p>
                    </div>
                </div>
                <a href="#kontak-bantuan" class="inline-flex min-h-11 shrink-0 items-center text-sm font-semibold underline underline-offset-4">Lihat kontak bantuan</a>
            </div>
        </div>
    </section>

    {{-- 3. Kapan sebaiknya mencari bantuan --}}
    <section class="bg-surface-alt" aria-labelledby="kapan">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <h2 id="kapan" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Kapan sebaiknya mencari bantuan</h2>
            <ul data-stagger class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($signs as $sign)
                    <li data-item class="flex items-center gap-4 rounded-2xl border border-border/60 bg-card p-5 shadow-lift">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full text-primary {{ $tones[$loop->index % 4] }}" aria-hidden="true"><x-icon name="{{ $sign['icon'] }}" class="size-5" /></span>
                        <p class="text-sm leading-relaxed text-text md:text-base">{{ $sign['text'] }}</p>
                    </li>
                @endforeach
                <li data-item class="flex items-center gap-4 rounded-2xl border border-l-4 border-border/60 border-l-crisis-text bg-card p-5 shadow-lift md:col-span-2">
                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-crisis-bg text-crisis-text" aria-hidden="true"><x-icon name="heart" class="size-5" /></span>
                    <p class="text-sm font-semibold text-text md:text-base">Kamu punya pikiran untuk menyakiti diri sendiri.</p>
                </li>
            </ul>
        </div>
    </section>

    {{-- 4. Kontak bantuan (hanya dari config, OQ-3), ditutup catatan sebaris --}}
    <section id="kontak-bantuan" class="scroll-mt-24 bg-background" aria-labelledby="kontak">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-14 md:pt-14 md:pb-20">
            <h2 id="kontak" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Kontak bantuan</h2>

            @if (count($contacts) === 0)
                <div data-reveal="pop" class="mx-auto mt-6 flex max-w-xl flex-col items-center rounded-3xl bg-surface-alt px-5 py-10 text-center">
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
                        <li data-item data-lift class="flex flex-col rounded-2xl border border-border/60 bg-card p-5 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover md:p-6">
                            <span class="grid size-11 place-items-center rounded-full text-primary {{ $tones[$loop->index % 4] }}" aria-hidden="true">
                                <x-icon name="phone" class="size-5" />
                            </span>
                            {{-- Tiga zona tetap agar semua kartu sejajar: nama (tinggi dua baris), kontak, catatan di dasar --}}
                            <h3 class="mt-4 text-lg leading-snug font-bold sm:min-h-[3.2rem]">{{ $contact['name'] ?? '' }}</h3>

                            <div class="mt-2 flex flex-col">
                                @if (filled($phone))
                                    <a href="tel:{{ preg_replace('/[^0-9+,]/', '', preg_replace('/\s*(?:ext\.?|ekstensi)\s*(\d+)/i', ',$1', $phone)) }}" class="inline-flex min-h-11 items-center font-semibold text-brand-strong underline underline-offset-4">{{ $phone }}</a>
                                @endif
                                @if (is_string($link) && str_starts_with($link, 'https://'))
                                    <a href="{{ $link }}" rel="noopener noreferrer" class="inline-flex min-h-11 items-center text-sm font-medium break-all text-brand-strong underline underline-offset-4">{{ $link }}</a>
                                @endif
                            </div>

                            @if ($isDummy || filled($contact['note'] ?? null))
                                <div class="mt-auto pt-4">
                                    <p class="border-t border-border/60 pt-4 text-sm {{ $isDummy ? 'font-medium text-warning' : 'text-text-secondary' }}">
                                        {{ $isDummy ? 'Contoh sementara, bukan kontak yang sebenarnya.' : $contact['note'] }}
                                    </p>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Catatan sebaris (pola Asesmen, Artikel, Tentang) --}}
            <div data-reveal class="mx-auto mt-8 flex max-w-2xl items-start gap-2.5 text-sm leading-relaxed text-text-secondary" role="note">
                <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-primary" />
                <p>RelaxBoss bukan psikolog atau layanan medis, dan hasil Asesmen bukan diagnosis.</p>
            </div>
        </div>
    </section>
@endsection
