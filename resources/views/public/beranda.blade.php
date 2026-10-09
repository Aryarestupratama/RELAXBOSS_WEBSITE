@extends('layouts.public')

@section('title', 'RelaxBoss: Asesmen, Mood Tracker, dan RelaxMate')
@section('description', 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.')

@section('content')
    @php $img = asset('images/landing'); @endphp

    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 py-12 lg:grid-cols-[45fr_55fr] lg:py-16">
            <div>
                <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">
                    Kita yang <span class="text-brand-strong underline decoration-brand decoration-4 underline-offset-8">mengendalikan stres</span>, bukan sebaliknya.
                </h1>
                <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">
                    Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja. Gratis, langsung dari browser.
                </p>
                <div data-load class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
                    <x-button-link href="{{ url('/asesmen') }}" variant="outline">Lihat asesmen</x-button-link>
                </div>
            </div>

            <div data-load="pop" class="relative overflow-hidden rounded-xl">
                <img src="{{ $img }}/hero-surf.webp" width="1536" height="1024" fetchpriority="high"
                     alt="RelaxMate, robot pendamping, berselancar tenang di atas ombak emosi bersama karakter Stres, Cemas, Sedih, dan Lega."
                     class="h-auto w-full">
                {{-- Label emosi: hiasan, dipasang lewat kode agar teks tajam dan tidak salah eja --}}
                <div class="hidden sm:block" aria-hidden="true">
                    <span data-float class="absolute top-[33%] left-[43%] -translate-x-1/2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card">Stres</span>
                    <span data-float class="absolute top-[58%] left-[37%] -translate-x-1/2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card">Cemas</span>
                    <span data-float class="absolute top-[80%] left-[40%] -translate-x-1/2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card">Sedih</span>
                    <span data-float class="absolute top-[45%] left-[86%] -translate-x-1/2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card">Lega</span>
                    <span data-float class="absolute top-[12%] left-[72%] -translate-x-1/2 rounded-full bg-card px-3 py-1 text-xs font-medium text-text shadow-card">Tenang</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Tiga fitur --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="judul-fitur">
        <h2 id="judul-fitur" data-reveal class="text-2xl md:text-3xl">Tiga cara merawat dirimu</h2>
        <div data-stagger class="mt-8 grid gap-4 lg:grid-cols-3">
            <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-mood-yellow p-5 text-text shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                    <img src="{{ $img }}/asesmen-checklist.webp" width="700" height="700" loading="lazy" alt="" data-tilt="4" class="size-32 object-contain">
                </div>
                <div>
                    <h3 class="text-center text-2xl font-semibold">Asesmen</h3>
                    <p class="mt-2 text-center">Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu.</p>
                </div>
            </article>
            <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-mood-green p-5 text-text shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                    <img src="{{ $img }}/mood-tracker.webp" width="700" height="700" loading="lazy" alt="" data-tilt="4" class="size-40 object-contain">
                </div>
                <div>
                    <h3 class="text-center text-2xl font-semibold">Mood Tracker</h3>
                    <p class="mt-2 text-center">Catat perasaanmu dan lihat polanya dalam seminggu.</p>
                </div>
            </article>
            <article data-item data-lift class="flex flex-col gap-5 rounded-xl bg-brand p-5 text-on-primary shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                <div class="mx-auto grid size-44 place-items-center overflow-hidden rounded-full bg-card">
                    <img src="{{ $img }}/robot-wave.webp" width="800" height="800" loading="lazy" alt="" data-tilt="-4" class="size-36 object-contain">
                </div>
                <div>
                    <h3 class="text-center text-2xl font-semibold">RelaxMate</h3>
                    <p class="mt-2 text-center">Teman bicara berbasis AI. Bukan psikolog atau layanan medis.</p>
                </div>
            </article>
        </div>
    </section>

    {{-- Langkah: urutan nyata, jadi memakai daftar bernomor --}}
    <section class="bg-neutral-soft" aria-labelledby="judul-langkah">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 md:py-16 lg:grid-cols-2">
            <div>
                <h2 id="judul-langkah" data-reveal="left" class="text-[1.75rem] md:text-4xl">Pelan-pelan juga tidak <span class="whitespace-nowrap">apa-apa.</span></h2>
                <ol data-stagger="left" class="mt-8 space-y-3">
                    <li data-item class="flex items-start gap-4 rounded-xl bg-card p-4 shadow-card transition duration-300 hover:translate-x-1 hover:shadow-popover">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-mood-yellow font-semibold text-text" aria-hidden="true">1</span>
                        <div><h3>Daftar gratis</h3><p class="text-text-secondary">Buat akun, lalu verifikasi emailmu.</p></div>
                    </li>
                    <li data-item class="flex items-start gap-4 rounded-xl bg-card p-4 shadow-card transition duration-300 hover:translate-x-1 hover:shadow-popover">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-mood-green font-semibold text-text" aria-hidden="true">2</span>
                        <div><h3>Kenali kondisimu</h3><p class="text-text-secondary">Kerjakan asesmen dan catat mood harianmu.</p></div>
                    </li>
                    <li data-item class="flex items-start gap-4 rounded-xl bg-card p-4 shadow-card transition duration-300 hover:translate-x-1 hover:shadow-popover">
                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-brand font-semibold text-on-primary" aria-hidden="true">3</span>
                        <div><h3>Bercerita kapan saja</h3><p class="text-text-secondary">RelaxMate siap mendengarkan, kapan pun kamu butuh.</p></div>
                    </li>
                </ol>
            </div>
            <img src="{{ $img }}/kamar-kos.webp" width="1200" height="800" loading="lazy"
                 alt="RelaxMate menemani seorang mahasiswa mengerjakan tugas di kamar pada malam hari."
                 data-reveal="right" class="h-auto w-full rounded-xl">
        </div>
    </section>

    {{-- RelaxMate --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16" aria-labelledby="judul-relaxmate">
        <div data-reveal class="grid items-center gap-8 rounded-xl border border-border bg-card p-5 md:p-8 lg:grid-cols-2">
            <div>
                <h2 id="judul-relaxmate" class="text-2xl md:text-3xl">Ada yang mendengarkan, kapan saja</h2>
                <p class="mt-4 max-w-prose text-text-secondary">
                    Ceritakan perasaanmu setelah kuliah, saat tugas menumpuk, atau kapan pun kamu butuh teman bicara.
                    RelaxMate adalah AI, bukan psikolog atau layanan medis, dan akan mengarahkanmu ke bantuan nyata bila diperlukan.
                </p>
                <div class="mt-6">
                    <x-button-link href="{{ url('/daftar') }}" variant="outline">Coba sapa RelaxMate</x-button-link>
                </div>
            </div>
            <img src="{{ $img }}/relaxmate-ngobrol.webp" width="1200" height="900" loading="lazy"
                 alt="RelaxMate duduk bersila dan mendengarkan cerita sebuah karakter mood berwarna kuning."
                 data-reveal="pop" class="h-auto w-full rounded-xl">
        </div>
    </section>

    {{-- Ruang aman --}}
    <section class="bg-neutral-soft" aria-labelledby="judul-aman">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <h2 id="judul-aman" data-reveal class="text-2xl md:text-3xl">Ruang aman untuk mahasiswa</h2>
            <div data-stagger class="mt-8 grid gap-4 md:grid-cols-3">
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="block size-10 rounded-full bg-mood-yellow" aria-hidden="true"></span>
                    <h3 class="mt-4">Bukan diagnosis</h3>
                    <p class="mt-2 text-text-secondary">Hasil asesmen adalah gambaran awal untuk membantumu mengenali kondisi, bukan penilaian medis.</p>
                </article>
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="block size-10 rounded-full bg-mood-green" aria-hidden="true"></span>
                    <h3 class="mt-4">Privasi dijaga</h3>
                    <p class="mt-2 text-text-secondary">Isi catatan dan percakapanmu dienkripsi, dan tinjauan kualitas dilakukan tanpa identitasmu.</p>
                </article>
                <article data-item data-lift class="rounded-xl bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
                    <span class="block size-10 rounded-full bg-brand" aria-hidden="true"></span>
                    <h3 class="mt-4">Jalur ke profesional</h3>
                    <p class="mt-2 text-text-secondary">Selalu ada arahan dan kontak bantuan bila kamu butuh pendampingan lebih lanjut.</p>
                </article>
            </div>
            <x-disclaimer data-reveal class="mt-8">
                RelaxBoss bukan psikolog atau layanan medis. Untuk penilaian yang tepat, bicarakan dengan tenaga profesional.
                <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Lihat jalur konsultasi</a>.
            </x-disclaimer>
        </div>
    </section>

    {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
    @if (filled($umb))
        <section class="mx-auto max-w-3xl px-4 pt-12" aria-labelledby="judul-umb">
            <h2 id="judul-umb">Kaitan akademik</h2>
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
