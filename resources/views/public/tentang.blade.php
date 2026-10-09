@extends('layouts.public')

@section('title', 'Tentang RelaxBoss')
@section('description', 'RelaxBoss membantu mahasiswa mengenali dan mengelola stres lewat asesmen, mood tracker, dan RelaxMate, teman bicara berbasis AI.')

@section('content')
    {{-- DRAF SEMENTARA: teks dibaca founder sebelum launch (TASK-030). --}}
    @php
        $img = asset('images/landing');
        // Ilustrasi hero khusus halaman Tentang (opsional). Bila berkas belum ada, pakai maskot Beranda.
        $heroFile = file_exists(public_path('images/about/tentang-hero.webp'))
            ? asset('images/about/tentang-hero.webp')
            : $img.'/robot-wave.webp';
    @endphp

    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-2 lg:py-16">
            <div>
                <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">
                    Tentang <span class="text-brand-strong underline decoration-brand decoration-4 underline-offset-8">RelaxBoss</span>
                </h1>
                <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">
                    Teman yang sederhana, gratis, dan ramah untuk membantu mahasiswa mengenali dan mengelola stres lebih awal.
                </p>
                <div data-load class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
                    <x-button-link href="{{ url('/asesmen') }}" variant="outline">Lihat asesmen</x-button-link>
                </div>
            </div>

            <div data-load="pop" class="relative mx-auto w-full max-w-md">
                <div class="grid aspect-square place-items-center overflow-hidden rounded-xl border border-border bg-card p-6 shadow-card">
                    <img src="{{ $heroFile }}" width="900" height="900" fetchpriority="high"
                         alt="RelaxMate, robot pendamping, melambai ramah di antara karakter Stres, Cemas, Sedih, dan Lega."
                         class="h-auto w-full max-w-72 object-contain">
                </div>
                {{-- Label emosi: hiasan, dipasang lewat kode agar teks tajam --}}
                <div class="hidden sm:block" aria-hidden="true">
                    <span data-float class="absolute top-5 left-5 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-red"></span>Stres</span>
                    <span data-float class="absolute top-8 right-5 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-yellow"></span>Cemas</span>
                    <span data-float class="absolute bottom-8 left-5 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-blue"></span>Sedih</span>
                    <span data-float class="absolute right-5 bottom-5 inline-flex items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card"><span class="size-3 rounded-full bg-mood-green"></span>Lega</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Visi --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="visi">
        <div class="grid items-center gap-8 lg:grid-cols-[7fr_5fr] lg:gap-12">
            <div data-reveal="left">
                <h2 id="visi" class="text-2xl md:text-3xl">Visi</h2>
                <p class="mt-4 max-w-prose text-lg text-text-secondary">
                    RelaxBoss ingin membantu mahasiswa mengenali dan mengelola stres lebih awal, dengan cara yang sederhana, gratis, dan ramah.
                    Kami percaya kamu yang memegang kendali atas kondisimu, bukan sebaliknya.
                </p>
            </div>

            <figure data-reveal="right" class="flex min-h-48 flex-col justify-between rounded-xl bg-brand p-8 text-on-primary shadow-card lg:p-10">
                <svg class="size-6 fill-mood-yellow" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.8 6.5L20 10l-6.2 1.5L12 18l-1.8-6.5L4 10l6.2-1.5z"/></svg>
                <blockquote class="mt-6 text-2xl leading-snug font-semibold">
                    &ldquo;Kamu yang memegang kendali, bukan stres.&rdquo;
                </blockquote>
            </figure>
        </div>
    </section>

    {{-- Yang bisa kamu lakukan --}}
    <section class="bg-neutral-soft" aria-labelledby="fitur">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 id="fitur" data-reveal class="text-2xl md:text-3xl">Yang bisa kamu lakukan</h2>
            <div data-stagger class="mt-8 grid gap-4 lg:grid-cols-3">
                <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-mood-yellow p-5 text-text shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                        <img src="{{ $img }}/asesmen-checklist.webp" width="700" height="700" loading="lazy" alt="" data-tilt="4" class="size-32 object-contain">
                    </div>
                    <div>
                        <h3 class="text-center text-2xl font-semibold">Asesmen</h3>
                        <p class="mt-2 text-center">Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.</p>
                    </div>
                </article>
                <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-mood-green p-5 text-text shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                        <img src="{{ $img }}/mood-tracker.webp" width="700" height="700" loading="lazy" alt="" data-tilt="4" class="size-40 object-contain">
                    </div>
                    <div>
                        <h3 class="text-center text-2xl font-semibold">Mood Tracker</h3>
                        <p class="mt-2 text-center">Catat perasaanmu dari hari ke hari dan lihat polanya.</p>
                    </div>
                </article>
                <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-brand p-5 text-on-primary shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                        <img src="{{ $img }}/robot-wave.webp" width="800" height="800" loading="lazy" alt="" data-tilt="-4" class="size-36 object-contain">
                    </div>
                    <div>
                        <h3 class="text-center text-2xl font-semibold">RelaxMate</h3>
                        <p class="mt-2 text-center">Teman bicara berbasis AI untuk bercerita kapan saja. Bukan psikolog atau layanan medis.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- Tentang nama --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="nama">
        <h2 id="nama" data-reveal class="text-center text-2xl md:text-3xl">Tentang nama</h2>
        <div data-stagger class="relative mx-auto mt-10 grid max-w-4xl items-stretch gap-4 md:grid-cols-2 md:gap-6">
            <article data-item data-lift class="flex min-h-52 flex-col items-center justify-center rounded-xl bg-mood-green p-8 text-center text-text shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-10">
                <p class="text-4xl font-semibold" aria-hidden="true">Relax</p>
                <p class="mt-3 text-lg"><span class="font-semibold">&ldquo;Relax&rdquo;</span> adalah ajakan untuk tenang.</p>
            </article>
            <article data-item data-lift class="flex min-h-52 flex-col items-center justify-center rounded-xl bg-brand p-8 text-center text-on-primary shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-10">
                <p class="text-4xl font-semibold" aria-hidden="true">Boss</p>
                <p class="mt-3 text-lg"><span class="font-semibold">&ldquo;Boss&rdquo;</span> mengingatkan bahwa kamulah yang memegang kendali atas stresmu.</p>
            </article>
            <span class="pointer-events-none absolute top-1/2 left-1/2 z-10 hidden size-11 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border border-border bg-card text-xl font-semibold text-text shadow-popover md:grid" aria-hidden="true">+</span>
        </div>
    </section>

    {{-- Ruang aman --}}
    <section class="bg-neutral-soft" aria-labelledby="judul-aman">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 id="judul-aman" data-reveal class="text-2xl md:text-3xl">Ruang aman untuk mahasiswa</h2>
            <div data-stagger class="mt-8 grid gap-4 md:grid-cols-3">
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="grid size-10 place-items-center rounded-full bg-mood-yellow text-text" aria-hidden="true"><x-icon name="clipboard-check" class="size-5" /></span>
                    <h3 class="mt-4">Bukan diagnosis</h3>
                    <p class="mt-2 text-text-secondary">Hasil asesmen adalah gambaran awal untuk membantumu mengenali kondisi, bukan penilaian medis.</p>
                </article>
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="grid size-10 place-items-center rounded-full bg-mood-green text-text" aria-hidden="true"><x-icon name="smile" class="size-5" /></span>
                    <h3 class="mt-4">Privasi dijaga</h3>
                    <p class="mt-2 text-text-secondary">Isi catatan dan percakapanmu dienkripsi, dan tinjauan kualitas dilakukan tanpa identitasmu.</p>
                </article>
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="grid size-10 place-items-center rounded-full bg-brand text-on-primary" aria-hidden="true"><x-icon name="message-circle" class="size-5" /></span>
                    <h3 class="mt-4">Jalur ke profesional</h3>
                    <p class="mt-2 text-text-secondary">Selalu ada arahan dan kontak bantuan bila kamu butuh pendampingan lebih lanjut.</p>
                </article>
            </div>
            <x-disclaimer data-reveal class="mt-8">
                RelaxBoss bukan psikolog atau layanan medis. Kalau kamu butuh bantuan,
                <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">lihat halaman Konsultasi Profesional</a>.
            </x-disclaimer>
        </div>
    </section>

    {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
    @if (filled($umb))
        <section class="mx-auto max-w-3xl px-4 pt-12" aria-labelledby="umb">
            <h2 id="umb" class="text-2xl">Kaitan akademik</h2>
            <p class="mt-3 text-text-secondary">{{ $umb }}</p>
        </section>
    @endif

    {{-- Bantuan --}}
    <section class="mx-auto max-w-6xl px-4 pt-12" aria-label="Bantuan">
        <div data-reveal class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-crisis-bg p-5 text-crisis-text">
            <div>
                <h2 class="text-lg">Butuh bantuan sekarang?</h2>
                <p>Kamu tidak harus menghadapi ini sendirian.</p>
            </div>
            <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-2">Lihat halaman Konsultasi Profesional</a>
        </div>
    </section>

    {{-- Penutup --}}
    <section class="mx-auto max-w-6xl px-4 pt-14 pb-12" aria-labelledby="judul-penutup">
        <div data-reveal class="text-center">
            <h2 id="judul-penutup" class="text-[1.75rem] md:text-5xl">Mulai dari satu langkah kecil.</h2>
            <p class="mx-auto mt-4 max-w-prose text-text-secondary">Mulai dari asesmen atau catat mood pertamamu hari ini.</p>
            <div class="mt-8"><x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link></div>
        </div>
        <img src="{{ $img }}/taman-mood.webp" width="1800" height="600" loading="lazy"
             alt="RelaxMate menyiram taman berisi tanaman yang mewakili berbagai perasaan."
             data-reveal="pop" class="mx-auto mt-10 h-auto w-full max-w-5xl rounded-xl">
    </section>
@endsection
