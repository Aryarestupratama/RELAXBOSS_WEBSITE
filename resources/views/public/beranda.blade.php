@extends('layouts.public')

@section('title', 'RelaxBoss: Asesmen, Mood Tracker, dan RelaxMate')
@section('description', 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.')

@php
    // Kartu emosi (bagian 2). `image` kosong = sementara memakai ikon Lucide sampai ilustrasinya ada.
    $emotions = [
        ['name' => 'Gangguan Kecemasan', 'text' => 'Pikiran sulit berhenti berputar dan cemas pada hal yang belum terjadi.', 'tone' => 'lavender', 'offset' => 'lg:mt-8', 'image' => 'emosi-kecemasan.webp', 'width' => 720, 'height' => 647, 'alt' => 'Ilustrasi mahasiswa yang cemas memeluk lutut, dengan coretan pikiran di atas kepalanya.', 'icon' => null],
        ['name' => 'Depresi', 'text' => 'Rasa sedih dan hampa yang berlangsung lama, hingga minat pada hal yang disukai hilang.', 'tone' => 'sky', 'offset' => 'lg:mt-0', 'image' => 'emosi-depresi.webp', 'width' => 720, 'height' => 557, 'alt' => 'Ilustrasi mahasiswa yang murung duduk di tepi tempat tidur, dengan awan kecil di atas kepalanya.', 'icon' => null],
        ['name' => 'Insomnia', 'text' => 'Sulit tidur atau sering terbangun, sehingga tubuh tidak sempat pulih.', 'tone' => 'mint', 'offset' => 'lg:mt-5', 'image' => 'emosi-insomnia.webp', 'width' => 720, 'height' => 476, 'alt' => 'Ilustrasi mahasiswa yang terjaga di tempat tidur pada malam hari, dengan bulan, bintang, dan jam weker di samping.', 'icon' => null],
        ['name' => 'Stres Akademik', 'text' => 'Tugas, ujian, dan tenggat menumpuk sampai terasa tak ada habisnya.', 'tone' => 'peach', 'offset' => 'lg:mt-1', 'image' => 'emosi-stres-akademik.webp', 'width' => 720, 'height' => 554, 'alt' => 'Ilustrasi mahasiswa yang kewalahan di meja belajar dengan tumpukan buku, laptop, dan kertas berterbangan.', 'icon' => null],
    ];

    // Kartu fitur (bagian 4). Tamu diarahkan ke pendaftaran untuk fitur yang butuh akun.
    $features = [
        ['name' => 'Mood Tracker', 'text' => 'Catat suasana hati harian dan lihat polanya dari waktu ke waktu.', 'tone' => 'sky', 'icon' => 'smile', 'href' => auth()->check() ? url('/mood') : url('/daftar')],
        ['name' => 'RelaxMate', 'text' => 'Teman bicara AI yang mendengarkan dan menyarankan langkah kecil.', 'tone' => 'mint', 'icon' => 'message-circle', 'href' => auth()->check() ? url('/relaxmate') : url('/daftar')],
        ['name' => 'Asesmen', 'text' => 'Kuesioner singkat untuk mengenali kondisi diri. Bukan diagnosis.', 'tone' => 'peach', 'icon' => 'clipboard-check', 'href' => url('/asesmen')],
        ['name' => 'Artikel', 'text' => 'Bacaan ringan tentang menjaga kesehatan mental sehari-hari.', 'tone' => 'lavender', 'icon' => 'file-text', 'href' => url('/artikel')],
    ];

    // Panduan (bagian 5): urutan nyata, jadi daftar bernomor.
    $steps = [
        ['name' => 'Kenali perasaan', 'text' => 'Catat suasana hatimu hari ini.'],
        ['name' => 'Pahami polanya', 'text' => 'Lihat grafik mood dan hasil asesmenmu.'],
        ['name' => 'Tenangkan diri', 'text' => 'Bicara dengan RelaxMate dan coba saran kecilnya.'],
        ['name' => 'Cari dukungan', 'text' => 'Hubungi tenaga profesional bila terasa berat.'],
    ];
@endphp

@section('content')
    {{-- 1. Hero --}}
    <section class="bg-background" aria-labelledby="judul-hero">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-8 md:pt-14 md:pb-10">
            <div class="max-w-3xl">
                <h1 id="judul-hero" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">
                    Anda yang mengendalikan stres, bukan sebaliknya.
                </h1>
                <p data-load class="mt-4 max-w-lg text-base text-text-secondary">
                    RelaxBoss membantu Anda mencatat suasana hati, berbicara dengan teman AI yang mendengarkan, dan mengenali diri lewat asesmen singkat, satu langkah kecil setiap hari.
                </p>
                <div data-load class="mt-6">
                    <x-account-cta label="Mulai Mengendalikan Stres Anda" class="rounded-full! px-6 text-sm" />
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Masalah umum: kartu arsip bertingkat (tanpa judul bagian) --}}
    <section class="bg-background" aria-label="Kondisi yang umum dialami">
        <div class="mx-auto max-w-6xl px-4 pb-10 md:pb-14">
            <div data-stagger class="grid items-start gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($emotions as $emotion)
                    <div data-item class="{{ $emotion['offset'] }}">
                        <x-ui.card tag="article" :tone="$emotion['tone']" data-lift class="gap-2! px-5 transition-shadow duration-300 hover:shadow-lift-hover">
                            <x-ui.card.header class="px-0! gap-2">
                                <x-ui.card.title tag="h2">{{ $emotion['name'] }}</x-ui.card.title>
                                <x-ui.card.description class="text-[0.8125rem]">{{ $emotion['text'] }}</x-ui.card.description>
                            </x-ui.card.header>
                            <x-ui.card.content class="px-0!">
                                @if ($emotion['image'])
                                    <img src="{{ asset('images/landing/'.$emotion['image']) }}" width="{{ $emotion['width'] }}" height="{{ $emotion['height'] }}" alt="{{ $emotion['alt'] }}" loading="lazy" class="mt-1 h-36 w-full object-contain">
                                @else
                                    <div class="mt-1 grid h-36 place-items-center" aria-hidden="true">
                                        <span class="grid size-20 place-items-center rounded-full bg-card/60 text-primary"><x-icon name="{{ $emotion['icon'] }}" class="size-9" /></span>
                                    </div>
                                @endif
                            </x-ui.card.content>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 3. Tentang --}}
    <section class="bg-surface-alt" aria-labelledby="judul-tentang">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-14">
                <div data-reveal>
                    <h2 id="judul-tentang" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Apa itu RelaxBoss?</h2>
                    <p class="mt-3 max-w-prose text-base text-text-secondary">
                        RelaxBoss adalah ruang aman untuk mahasiswa dan pekerja mengelola stres sehari-hari. Di sini Anda bisa mencatat suasana hati, berbicara dengan RelaxMate, dan mengenali kondisi diri lewat asesmen.
                        RelaxBoss bukan layanan darurat dan bukan pengganti tenaga profesional.
                    </p>
                    <div class="mt-5">
                        <x-button-link href="{{ url('/tentang') }}" variant="outline" class="group gap-2 rounded-full! border-2! bg-card px-5 text-sm">
                            Kenali kami lebih dekat
                            <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                        </x-button-link>
                    </div>
                </div>
                <div data-reveal="pop" class="flex justify-center">
                    <img src="{{ asset('images/logo-relaxboss.png') }}" width="192" height="192" alt="Logo RelaxBoss" loading="lazy" class="size-32 rounded-3xl shadow-lift-hover md:size-40">
                </div>
            </div>

            {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
            @if (filled($umb))
                <div class="mt-10 max-w-3xl" data-reveal>
                    <h3 class="text-lg font-semibold">Kaitan akademik</h3>
                    <p class="mt-3 text-text-secondary">{{ $umb }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- 4. Fitur --}}
    <section id="fitur" class="bg-background" aria-labelledby="judul-fitur">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <div data-reveal class="mx-auto max-w-xl text-center">
                <h2 id="judul-fitur" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Fitur yang ditawarkan</h2>
                <p class="mt-3 text-base text-text-secondary">Alat sederhana yang bisa dipakai kapan saja, tanpa perlu rumit.</p>
            </div>
            <div data-stagger class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $feature)
                    <div data-item>
                        <x-ui.card tag="article" :tone="$feature['tone']" radius="rounded-3xl" data-lift class="group relative min-h-56 gap-2! px-5 pb-16 transition-shadow duration-300 hover:shadow-lift-hover">
                            <x-ui.card.header class="px-0!">
                                <span class="grid size-12 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true"><x-icon name="{{ $feature['icon'] }}" class="size-5" /></span>
                                <x-ui.card.title class="mt-1 text-xl">{{ $feature['name'] }}</x-ui.card.title>
                                <x-ui.card.description class="text-[0.8125rem]">{{ $feature['text'] }}</x-ui.card.description>
                            </x-ui.card.header>
                            <a href="{{ $feature['href'] }}" aria-label="Coba {{ $feature['name'] }}"
                               class="absolute right-0 bottom-0 grid h-12 w-14 place-items-center rounded-tl-[20px] rounded-br-3xl bg-background text-primary transition-colors duration-200 group-hover:bg-nav-hover group-hover:text-on-primary">
                                <x-icon name="arrow-up-right" class="size-5 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                            </a>
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Panduan --}}
    <section class="bg-surface-alt" aria-labelledby="judul-panduan">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <div class="grid items-start gap-8 lg:grid-cols-2 lg:gap-14">
                <div data-reveal class="max-w-md">
                    <h2 id="judul-panduan" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Empat langkah menuju perasaan lebih baik</h2>
                    <p class="mt-3 text-base text-text-secondary">Tidak perlu terburu-buru. Ikuti satu per satu, sesuai kecepatan Anda.</p>
                    <div class="mt-5">
                        <x-account-cta label="Siap Merasa Lebih Baik" class="rounded-full! px-6 text-sm" />
                    </div>
                </div>
                <ol data-stagger class="relative space-y-6">
                    <span class="absolute top-5 bottom-5 left-[17px] w-px bg-border" aria-hidden="true"></span>
                    @foreach ($steps as $step)
                        <li data-item class="relative grid grid-cols-[2.25rem_1fr] gap-4">
                            <span class="relative z-10 grid size-9 place-items-center rounded-full border-[1.5px] border-primary bg-card text-sm font-bold" aria-hidden="true">{{ $loop->iteration }}</span>
                            <div>
                                <h3 class="text-base font-semibold">{{ $step['name'] }}</h3>
                                <p class="mt-0.5 text-sm text-text-secondary">{{ $step['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- 6. Ajakan akhir --}}
    <section class="bg-background" aria-labelledby="judul-penutup">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <x-help-box data-reveal class="mb-8" />
            {{-- Ilustrasi menyusul: tambahkan sebagai kolom kedua (ubah wadah menjadi lg:grid-cols-2) --}}
            <div data-reveal class="rounded-3xl bg-surface-alt px-5 py-8 md:px-10 md:py-10">
                <div class="max-w-xl">
                    <h2 id="judul-penutup" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Siap merasa lebih baik?</h2>
                    <p class="mt-3 text-base text-text-secondary">Tidak perlu menunggu semuanya membaik. Mulai hari ini, dan kami temani Anda.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-account-cta label="Daftar Gratis" class="rounded-full! px-6 text-sm" />
                        <x-button-link href="#fitur" variant="outline" class="rounded-full! border-2! bg-card px-6 text-sm">Pelajari Fitur</x-button-link>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
