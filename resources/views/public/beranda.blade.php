@extends('layouts.public')

@section('title', 'RelaxBoss: Asesmen, Mood Tracker, dan RelaxMate')
@section('description', 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.')

@php
    // Kartu emosi (bagian 2). `image` kosong = sementara memakai ikon Lucide sampai ilustrasinya ada.
    $emotions = [
        ['name' => 'Gangguan Kecemasan', 'text' => 'Pikiran sulit berhenti berputar dan cemas pada hal yang belum terjadi.', 'image' => 'emosi-kecemasan.webp', 'width' => 720, 'height' => 647, 'alt' => 'Ilustrasi mahasiswa yang cemas memeluk lutut, dengan coretan pikiran di atas kepalanya.', 'icon' => null],
        ['name' => 'Depresi', 'text' => 'Rasa sedih dan hampa yang berlangsung lama, hingga minat pada hal yang disukai hilang.', 'image' => 'emosi-depresi.webp', 'width' => 720, 'height' => 557, 'alt' => 'Ilustrasi mahasiswa yang murung duduk di tepi tempat tidur, dengan awan kecil di atas kepalanya.', 'icon' => null],
        ['name' => 'Insomnia', 'text' => 'Sulit tidur atau sering terbangun, sehingga tubuh tidak sempat pulih.', 'image' => 'emosi-insomnia.webp', 'width' => 720, 'height' => 476, 'alt' => 'Ilustrasi mahasiswa yang terjaga di tempat tidur pada malam hari, dengan bulan, bintang, dan jam weker di samping.', 'icon' => null],
        ['name' => 'Stres Akademik', 'text' => 'Tugas, ujian, dan tenggat menumpuk sampai terasa tak ada habisnya.', 'image' => 'emosi-stres-akademik.webp', 'width' => 720, 'height' => 554, 'alt' => 'Ilustrasi mahasiswa yang kewalahan di meja belajar dengan tumpukan buku, laptop, dan kertas berterbangan.', 'icon' => null],
    ];

    // Kartu fitur (bagian 4). Tamu diarahkan ke pendaftaran untuk fitur yang butuh akun.
    $features = [
        ['name' => 'Mood Tracker', 'text' => 'Catat suasana hati harian dan lihat polanya dari waktu ke waktu.', 'tone' => 'sky', 'icon' => 'smile', 'href' => auth()->check() ? url('/mood') : url('/daftar')],
        ['name' => 'RelaxMate', 'text' => 'Teman bicara AI yang mendengarkan dan menyarankan langkah kecil.', 'tone' => 'mint', 'icon' => 'message-circle', 'href' => auth()->check() ? url('/relaxmate') : url('/daftar')],
        ['name' => 'Asesmen', 'text' => 'Kuesioner singkat untuk mengenali kondisi diri. Bukan diagnosis.', 'tone' => 'peach', 'icon' => 'clipboard-check', 'href' => url('/asesmen')],
        ['name' => 'Artikel', 'text' => 'Bacaan ringan tentang menjaga kesehatan mental sehari-hari.', 'tone' => 'lavender', 'icon' => 'file-text', 'href' => url('/artikel')],
    ];

    // Panduan (bagian 5): urutan nyata, jadi daftar bernomor. Di layar lebar kartu "disematkan" zig-zag di papan;
    // posisi dan kemiringan per kartu ada di `pos` (kelas lengkap agar terbaca Tailwind).
    $steps = [
        ['name' => 'Kenali perasaan', 'text' => 'Catat suasana hatimu hari ini.', 'icon' => 'smile', 'pos' => 'rotate-3 lg:absolute lg:top-0 lg:left-[12%] lg:rotate-6'],
        ['name' => 'Pahami polanya', 'text' => 'Lihat grafik mood dan hasil asesmenmu.', 'icon' => 'chart-line', 'pos' => '-rotate-3 lg:absolute lg:top-[140px] lg:right-[12%] lg:-rotate-6'],
        ['name' => 'Tenangkan diri', 'text' => 'Bicara dengan RelaxMate dan coba saran kecilnya.', 'icon' => 'message-circle', 'pos' => 'rotate-3 lg:absolute lg:top-[400px] lg:left-[12%] lg:rotate-6'],
        ['name' => 'Cari dukungan', 'text' => 'Hubungi tenaga profesional bila terasa berat.', 'icon' => 'life-buoy', 'pos' => '-rotate-3 lg:absolute lg:top-[540px] lg:right-[12%] lg:-rotate-6'],
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
                    Catat suasana hati, bercerita pada RelaxMate, dan kenali dirimu lewat asesmen, mulai dari satu langkah kecil hari ini.
                </p>
                <div data-load class="mt-6">
                    <x-account-cta label="Mulai Tenang" class="rounded-full! px-6 text-sm" />
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Masalah umum: kartu sama besar, ilustrasi di atas cahaya ungu, label dan deskripsi di panel bawah --}}
    <section class="bg-background" aria-label="Kondisi yang umum dialami">
        <div class="mx-auto max-w-6xl px-4 pb-10 md:pb-14">
            <div data-stagger class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($emotions as $emotion)
                    <div data-item class="h-full">
                        <x-ui.card tag="article" radius="rounded-3xl" flush data-lift class="h-full p-2 transition-shadow duration-300 hover:shadow-lift-hover">
                            <div class="relative z-10 grid h-52 place-items-end justify-items-center rounded-[20px] bg-[radial-gradient(ellipse_at_50%_55%,var(--soft-lavender),transparent_72%)]">
                                @if ($emotion['image'])
                                    <img src="{{ asset('images/landing/'.$emotion['image']) }}" width="{{ $emotion['width'] }}" height="{{ $emotion['height'] }}" alt="{{ $emotion['alt'] }}" loading="lazy" class="-mb-6 h-40 max-w-full object-contain">
                                @else
                                    <span class="mb-2 grid size-20 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="{{ $emotion['icon'] }}" class="size-9" /></span>
                                @endif
                            </div>
                            <div class="relative flex flex-1 flex-col gap-2 rounded-2xl border border-border/60 bg-card px-4 pt-9 pb-4">
                                <h2 class="w-fit rounded-full bg-soft-lavender px-3 py-1 text-xs font-semibold tracking-wide text-text uppercase">{{ $emotion['name'] }}</h2>
                                <p class="text-base leading-relaxed text-text">{{ $emotion['text'] }}</p>
                            </div>
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
                        RelaxBoss adalah ruang aman bagi mahasiswa dan pekerja untuk mengelola stres sehari-hari.
                        Bukan layanan darurat dan bukan pengganti tenaga profesional.
                    </p>
                    <div class="mt-5">
                        <x-button-link href="{{ url('/tentang') }}" variant="outline" class="group gap-2 rounded-full! border-2! bg-card px-5 text-sm">
                            Kenali Kami
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
                                <span class="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="{{ $feature['icon'] }}" class="size-5" /></span>
                                <x-ui.card.title class="mt-1 text-xl">{{ $feature['name'] }}</x-ui.card.title>
                                <x-ui.card.description class="text-[0.8125rem]">{{ $feature['text'] }}</x-ui.card.description>
                            </x-ui.card.header>
                            <x-ui.notch-corner :href="$feature['href']" :label="'Coba '.$feature['name']" />
                        </x-ui.card>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Panduan --}}
    <section class="relative overflow-hidden bg-surface-alt" aria-labelledby="judul-panduan">
        {{-- Garis buku tulis tipis dari token --border, memudar ke tepi --}}
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(color-mix(in_srgb,var(--border)_45%,transparent)_1px,transparent_1px)] bg-size-[100%_32px]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-y-0 left-0 w-1/4 bg-linear-to-r from-surface-alt to-transparent" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 w-1/4 bg-linear-to-l from-surface-alt to-transparent" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-10 md:py-14">
            <div data-reveal class="mx-auto max-w-xl text-center">
                <h2 id="judul-panduan" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Empat langkah menuju perasaan lebih baik</h2>
                <p class="mt-3 text-base text-text-secondary">Ikuti jalurnya dari kartu pertama. Tidak perlu terburu-buru, sesuai kecepatan Anda.</p>
            </div>

            <div class="relative mx-auto mt-10 max-w-[1000px] lg:h-[830px]">
                {{-- Jalur putus-putus penghubung kartu (hanya layar lebar, dekoratif) --}}
                <svg class="pointer-events-none absolute inset-0 hidden size-full lg:block" viewBox="0 0 1000 830" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M 260 140 C 520 140, 480 270, 740 270 C 990 270, 500 420, 260 535 C 20 650, 500 675, 740 675"
                          class="animate-dash-flow stroke-border" fill="none" stroke-width="2" stroke-dasharray="8 6" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                </svg>

                <ol role="list" data-stagger class="relative z-10 flex flex-col items-center gap-8 lg:block">
                    @foreach ($steps as $step)
                        <li data-item class="relative w-full max-w-[280px] transition-[scale] duration-300 hover:z-30 hover:scale-105 lg:w-[280px] {{ $step['pos'] }}">
                            <div class="rounded-[25px] border border-border/60 bg-card p-2 shadow-lift">
                                <x-icon name="pin" class="mx-auto mb-4 size-8 fill-current text-primary" />
                                <div class="flex min-h-40 flex-col rounded-[15px] border border-primary/15 bg-soft-lavender p-4">
                                    <div class="flex items-start justify-between">
                                        <span class="text-4xl leading-none font-bold text-primary" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                        <span class="grid size-11 place-items-center rounded-full bg-card text-primary" aria-hidden="true"><x-icon name="{{ $step['icon'] }}" class="size-5" /></span>
                                    </div>
                                    <h3 class="mt-4 text-xl leading-tight font-semibold">{{ $step['name'] }}</h3>
                                    <p class="mt-1 text-sm text-text-secondary">{{ $step['text'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div data-reveal class="mt-10 text-center lg:mt-4">
                <x-account-cta label="Mulai dari Langkah 1" class="rounded-full! px-6 text-sm" />
            </div>
        </div>
    </section>

    {{-- 6. Ajakan akhir --}}
    <section class="bg-background" aria-labelledby="judul-penutup">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
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
