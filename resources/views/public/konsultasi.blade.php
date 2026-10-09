@extends('layouts.public')

@section('title', 'Konsultasi Profesional | RelaxBoss')
@section('description', 'Kapan sebaiknya mencari bantuan profesional untuk stres, dan daftar kontak bantuan yang bisa kamu hubungi.')

@section('content')
    @php
        $img = asset('images/konsultasi');
        // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
        $circles = ['bg-mood-yellow text-text', 'bg-mood-green text-text', 'bg-brand text-on-primary'];
        // Butir ini bukan urutan langkah, jadi tanpa nomor.
        $signs = [
            ['icon' => 'moon', 'text' => 'Rasa tertekan berlangsung berminggu-minggu dan mengganggu kuliah, tidur, atau makanmu.'],
            ['icon' => 'smile', 'text' => 'Kamu sulit menikmati hal yang dulu kamu sukai.'],
            ['icon' => 'wind', 'text' => 'Kamu merasa kewalahan terus-menerus dan sulit mengatasinya sendiri.'],
            ['icon' => 'users', 'text' => 'Orang terdekat mulai mengkhawatirkan keadaanmu.'],
        ];
    @endphp

    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-2 lg:py-16">
            <div>
                <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">Konsultasi profesional</h1>
                <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">
                    RelaxBoss membantu mengenali kondisimu, tetapi tidak menggantikan psikolog atau tenaga kesehatan jiwa.
                    Bicara dengan profesional adalah langkah yang wajar dan tidak perlu menunggu sampai keadaan parah.
                </p>
                <div data-load class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="#kontak-bantuan">Lihat kontak bantuan</x-button-link>
                    <x-button-link href="{{ url('/asesmen') }}" variant="outline">Lihat asesmen</x-button-link>
                </div>
            </div>

            <div data-load="pop" class="mx-auto w-full max-w-md">
                <div class="overflow-hidden rounded-xl border border-border bg-neutral-soft shadow-card">
                    <img src="{{ $img }}/konsultasi-hero.webp" width="900" height="900" fetchpriority="high"
                         alt="RelaxMate, robot pendamping, duduk tenang di samping karakter perasaan biru yang lelah, dengan satu tangan bersandar lembut di dekatnya."
                         class="h-auto w-full">
                </div>
            </div>
        </div>
    </section>

    {{-- Kotak darurat --}}
    <section class="mx-auto max-w-6xl px-4 pt-10 md:pt-12" aria-labelledby="darurat">
        <div data-reveal class="flex flex-col items-start justify-between gap-4 rounded-xl border border-crisis-text/30 bg-crisis-bg p-5 text-crisis-text md:flex-row md:items-center lg:p-6">
            <div class="flex items-start gap-4">
                <span class="mt-1 shrink-0" aria-hidden="true"><x-icon name="shield" class="size-6" /></span>
                <div>
                    <h2 id="darurat" class="text-xl md:text-2xl">Kalau kamu sedang dalam bahaya</h2>
                    <p class="mt-2 max-w-2xl">
                        Kalau kamu berpikir untuk menyakiti dirimu sendiri, tolong hubungi bantuan sekarang atau minta seseorang yang kamu percaya menemanimu.
                        Kamu tidak harus menghadapi ini sendirian.
                    </p>
                </div>
            </div>
            <a href="#kontak-bantuan" class="inline-flex min-h-11 shrink-0 items-center font-medium underline underline-offset-2">Lihat kontak bantuan</a>
        </div>
    </section>

    {{-- Kapan sebaiknya mencari bantuan --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="kapan">
        <h2 id="kapan" data-reveal class="text-2xl md:text-3xl">Kapan sebaiknya mencari bantuan</h2>
        <ul data-stagger class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($signs as $sign)
                <li data-item class="flex items-center gap-4 rounded-xl border border-border bg-card p-5 shadow-card lg:p-6">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-neutral-soft text-primary" aria-hidden="true"><x-icon name="{{ $sign['icon'] }}" class="size-5" /></span>
                    <p class="text-text">{{ $sign['text'] }}</p>
                </li>
            @endforeach
            <li data-item class="flex items-center gap-4 rounded-xl border border-l-4 border-border border-l-crisis-text bg-card p-5 shadow-card md:col-span-2 lg:p-6">
                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-crisis-bg text-crisis-text" aria-hidden="true"><x-icon name="heart" class="size-5" /></span>
                <p class="font-medium text-text">Kamu punya pikiran untuk menyakiti diri sendiri.</p>
            </li>
        </ul>
    </section>

    {{-- Kontak bantuan --}}
    <section id="kontak-bantuan" class="scroll-mt-24 bg-neutral-soft" aria-labelledby="kontak">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 id="kontak" data-reveal class="text-2xl md:text-3xl">Kontak bantuan</h2>

            @if (count($contacts) === 0)
                <div data-reveal="pop" class="mx-auto mt-8 flex max-w-xl flex-col items-center rounded-xl border border-border bg-card p-8 text-center shadow-card">
                    <img src="{{ $img }}/konsultasi-kosong.webp" width="700" height="700" loading="lazy" alt="" class="size-44 object-contain">
                    <p class="mt-4 text-lg font-medium text-text">Daftar kontak sedang disiapkan.</p>
                    <div class="mt-6"><x-button-link href="{{ url('/asesmen') }}">Lihat asesmen</x-button-link></div>
                </div>
            @else
                <ul data-stagger class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                    @foreach ($contacts as $contact)
                        @php
                            $phone = $contact['phone'] ?? null;
                            $link = $contact['url'] ?? null;
                            $isDummy = (bool) ($contact['dummy'] ?? false);
                        @endphp
                        <li data-item data-lift class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                            <span class="grid size-12 place-items-center rounded-full {{ $circles[$loop->index % 3] }}" aria-hidden="true">
                                <x-icon name="phone" class="size-6" />
                            </span>
                            <h3 class="mt-4 text-xl font-semibold">{{ $contact['name'] ?? '' }}</h3>
                            @if (filled($phone))
                                <p class="mt-2">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2">{{ $phone }}</a>
                                </p>
                            @endif
                            @if (is_string($link) && str_starts_with($link, 'https://'))
                                <p>
                                    <a href="{{ $link }}" rel="noopener noreferrer" class="inline-flex min-h-11 items-center break-all font-medium text-brand-strong underline underline-offset-2">{{ $link }}</a>
                                </p>
                            @endif
                            @if (filled($contact['note'] ?? null) && ! $isDummy)
                                <p class="mt-1 text-sm text-text-secondary">{{ $contact['note'] }}</p>
                            @endif
                            @if ($isDummy)
                                <p class="mt-auto pt-4 text-sm font-medium text-warning">Contoh sementara, bukan kontak yang sebenarnya.</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    {{-- Disclaimer --}}
    <section class="mx-auto max-w-6xl px-4 py-12" aria-label="Catatan penting">
        <x-disclaimer data-reveal class="mx-auto max-w-3xl">
            RelaxBoss bukan psikolog atau layanan medis, dan hasil Asesmen bukan diagnosis.
        </x-disclaimer>
    </section>

    {{-- Penutup untuk tamu --}}
    @guest
        <section class="mx-auto max-w-6xl px-4 pb-14" aria-labelledby="judul-penutup">
            <div data-reveal class="text-center">
                <h2 id="judul-penutup" class="text-[1.75rem] md:text-5xl">Mulai dari satu langkah kecil.</h2>
                <p class="mx-auto mt-4 max-w-prose text-text-secondary">Mulai dari asesmen atau catat mood pertamamu hari ini.</p>
                <div class="mt-8"><x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link></div>
            </div>
        </section>
    @endguest
@endsection
