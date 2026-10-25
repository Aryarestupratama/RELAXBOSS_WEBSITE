@extends('layouts.public')

@section('title', 'RelaxBoss: Asesmen, Mood Tracker, dan RelaxMate')
@section('description', 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.')

@section('content')
    {{-- Hero --}}
    <section class="overflow-hidden bg-neutral-soft">
        <div class="mx-auto grid max-w-6xl items-end gap-10 px-4 pt-14 md:pt-20 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="lg:pb-16">
                <h1 data-load class="text-[2.5rem] leading-[1.08] font-semibold tracking-tight md:text-6xl">
                    Kita yang <span class="text-brand-strong">mengendalikan stres</span>, bukan sebaliknya.
                </h1>
                <p data-load class="mt-6 max-w-prose text-lg text-text-secondary">
                    Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja. Gratis, langsung dari browser.
                </p>
                <div data-load class="mt-8 flex flex-wrap gap-3">
                    <x-button-link href="{{ url('/daftar') }}">Daftar gratis</x-button-link>
                    <x-button-link href="{{ url('/asesmen') }}" variant="outline">Lihat asesmen</x-button-link>
                </div>
                <p data-load class="mt-6 text-sm text-text-muted">Untuk mahasiswa dan pekerja.</p>
            </div>

            <div data-load="pop" class="lg:self-end">
                <x-mood-ribbons label="Lima pita berwajah dalam warna mood, dari sedih sampai senang." class="mx-auto h-auto w-full max-w-xl lg:max-w-none" />
            </div>
        </div>
    </section>

    {{-- Tiga fitur --}}
    <section class="mx-auto max-w-6xl px-4 py-16 md:py-20" aria-labelledby="judul-fitur">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <div>
                <h2 id="judul-fitur" data-reveal class="text-[1.75rem] leading-tight tracking-tight md:text-4xl">Tiga cara merawat dirimu</h2>
                <div data-stagger class="mt-8 space-y-3">
                    <article data-item class="flex items-center gap-4 rounded-xl bg-mood-yellow/30 p-4 text-text">
                        <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-card text-primary"><x-icon name="clipboard-check" class="size-6" /></span>
                        <div>
                            <h3 class="text-lg font-semibold">Asesmen</h3>
                            <p>Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu.</p>
                        </div>
                    </article>
                    <article data-item class="flex items-center gap-4 rounded-xl bg-mood-green/30 p-4 text-text">
                        <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-card text-primary"><x-icon name="smile" class="size-6" /></span>
                        <div>
                            <h3 class="text-lg font-semibold">Mood Tracker</h3>
                            <p>Catat perasaanmu dan lihat polanya dalam seminggu.</p>
                        </div>
                    </article>
                    <article data-item class="flex items-center gap-4 rounded-xl bg-brand-soft/40 p-4 text-text">
                        <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-card text-primary"><x-icon name="message-circle" class="size-6" /></span>
                        <div>
                            <h3 class="text-lg font-semibold">RelaxMate</h3>
                            <p>Teman bicara berbasis AI yang siap mendengarkan kapan saja.</p>
                        </div>
                    </article>
                </div>
            </div>

            {{-- Contoh tampilan Mood Tracker (hiasan, bukan data pengguna) --}}
            <div data-reveal="pop" class="rounded-xl border border-border bg-neutral-soft p-5 md:p-8" aria-hidden="true">
                <div class="rounded-xl bg-card p-5 shadow-card">
                    <p class="text-sm font-medium text-text">Mood minggu ini</p>
                    <div class="mt-6 flex h-40 items-end justify-between gap-2">
                        @foreach ([['Sen', 'bg-mood-purple', 38], ['Sel', 'bg-mood-blue', 52], ['Rab', 'bg-mood-peach', 66], ['Kam', 'bg-mood-peach', 60], ['Jum', 'bg-mood-green', 80], ['Sab', 'bg-mood-green', 88], ['Min', 'bg-mood-yellow', 100]] as [$day, $tone, $height])
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                                <span class="w-full max-w-9 rounded-full {{ $tone }}" style="height: {{ $height }}%"></span>
                                <span class="text-xs text-text-muted">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-text-muted">Contoh tampilan</p>
            </div>
        </div>
    </section>

    {{-- Untuk siapa --}}
    <section class="bg-neutral-soft" aria-labelledby="judul-segmen">
        <div class="mx-auto max-w-6xl px-4 py-16 md:py-20">
            <h2 id="judul-segmen" data-reveal class="text-[1.75rem] leading-tight tracking-tight md:text-4xl">Untuk kamu yang sedang sibuk menjalani hari</h2>
            <div data-stagger class="mt-8 grid gap-4 md:grid-cols-2">
                <article data-item class="rounded-xl border border-border bg-card p-6 md:p-8">
                    <span class="grid size-12 place-items-center rounded-lg bg-mood-blue/40 text-primary"><x-icon name="graduation-cap" class="size-6" /></span>
                    <h3 class="mt-5 text-xl font-semibold">Mahasiswa</h3>
                    <p class="mt-2 text-text-secondary">Untuk hari-hari kuliah yang terasa berat.</p>
                    <ul class="mt-5 space-y-2 text-text">
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Tugas dan skripsi yang menumpuk</li>
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Cemas soal nilai dan masa depan</li>
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Jauh dari rumah dan merasa sendiri</li>
                    </ul>
                </article>
                <article data-item class="rounded-xl border border-border bg-card p-6 md:p-8">
                    <span class="grid size-12 place-items-center rounded-lg bg-mood-peach/40 text-primary"><x-icon name="briefcase" class="size-6" /></span>
                    <h3 class="mt-5 text-xl font-semibold">Pekerja</h3>
                    <p class="mt-2 text-text-secondary">Untuk lelah yang tidak hilang setelah akhir pekan.</p>
                    <ul class="mt-5 space-y-2 text-text">
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Beban kerja dan tenggat yang menekan</li>
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Sulit memisahkan kerja dan istirahat</li>
                        <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand" aria-hidden="true"></span>Butuh tempat bercerita yang tenang</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- Langkah: urutan nyata, jadi memakai daftar bernomor --}}
    <section class="mx-auto max-w-6xl px-4 py-16 md:py-20" aria-labelledby="judul-langkah">
        <h2 id="judul-langkah" data-reveal class="text-[1.75rem] leading-tight tracking-tight md:text-4xl">Pelan-pelan juga tidak <span class="whitespace-nowrap">apa-apa</span></h2>
        <ol data-stagger class="mt-10 grid gap-8 md:grid-cols-3">
            <li data-item class="border-t-2 border-primary pt-5">
                <span class="text-sm font-medium text-brand-strong" aria-hidden="true">01</span>
                <h3 class="mt-2 text-xl font-semibold">Daftar gratis</h3>
                <p class="mt-2 text-text-secondary">Buat akun, lalu verifikasi emailmu.</p>
            </li>
            <li data-item class="border-t-2 border-primary pt-5">
                <span class="text-sm font-medium text-brand-strong" aria-hidden="true">02</span>
                <h3 class="mt-2 text-xl font-semibold">Kenali kondisimu</h3>
                <p class="mt-2 text-text-secondary">Kerjakan asesmen dan catat mood harianmu.</p>
            </li>
            <li data-item class="border-t-2 border-primary pt-5">
                <span class="text-sm font-medium text-brand-strong" aria-hidden="true">03</span>
                <h3 class="mt-2 text-xl font-semibold">Bercerita kapan saja</h3>
                <p class="mt-2 text-text-secondary">RelaxMate siap mendengarkan, kapan pun kamu butuh.</p>
            </li>
        </ol>
    </section>

    {{-- RelaxMate --}}
    <section class="bg-neutral-soft" aria-labelledby="judul-relaxmate">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 md:py-20 lg:grid-cols-2 lg:gap-16">
            <div data-reveal>
                <h2 id="judul-relaxmate" class="text-[1.75rem] leading-tight tracking-tight md:text-4xl">Ada yang mendengarkan, kapan saja</h2>
                <p class="mt-5 max-w-prose text-text-secondary">
                    Ceritakan perasaanmu setelah kuliah, sepulang kerja, saat tugas atau tenggat menumpuk, atau kapan pun kamu butuh teman bicara.
                    RelaxMate adalah AI, bukan psikolog atau layanan medis, dan akan mengarahkanmu ke bantuan nyata bila diperlukan.
                </p>
                <div class="mt-6">
                    <x-button-link href="{{ url('/daftar') }}" variant="outline">Coba sapa RelaxMate</x-button-link>
                </div>
            </div>

            {{-- Contoh tampilan percakapan (hiasan) --}}
            <div data-reveal="pop" aria-hidden="true">
                <div class="rounded-xl bg-brand-soft/35 p-5 md:p-8">
                    <div class="space-y-3 rounded-xl bg-card p-5 shadow-card">
                        <p class="ml-auto w-fit max-w-[85%] rounded-xl rounded-br-sm bg-primary px-4 py-2.5 text-on-primary">Hari ini rasanya capek banget.</p>
                        <p class="w-fit max-w-[85%] rounded-xl rounded-bl-sm bg-neutral-soft px-4 py-2.5 text-text">Terdengar berat ya. Mau cerita, apa yang paling bikin kamu capek hari ini?</p>
                        <p class="ml-auto w-fit max-w-[85%] rounded-xl rounded-br-sm bg-primary px-4 py-2.5 text-on-primary">Kerjaan numpuk, aku nggak sempat istirahat.</p>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-text-muted">Contoh tampilan percakapan</p>
            </div>
        </div>
    </section>

    {{-- Ruang aman --}}
    <section class="mx-auto max-w-6xl px-4 py-16 md:py-20" aria-labelledby="judul-aman">
        <h2 id="judul-aman" data-reveal class="text-[1.75rem] leading-tight tracking-tight md:text-4xl">Ruang aman untuk mahasiswa dan pekerja</h2>
        <div data-stagger class="mt-8 grid gap-4 md:grid-cols-3">
            <article data-item class="rounded-xl border border-border bg-card p-6">
                <span class="grid size-11 place-items-center rounded-lg bg-mood-yellow/40 text-primary"><x-icon name="info" class="size-5" /></span>
                <h3 class="mt-4">Bukan diagnosis</h3>
                <p class="mt-2 text-text-secondary">Hasil asesmen adalah gambaran awal untuk membantumu mengenali kondisi, bukan penilaian medis.</p>
            </article>
            <article data-item class="rounded-xl border border-border bg-card p-6">
                <span class="grid size-11 place-items-center rounded-lg bg-mood-green/40 text-primary"><x-icon name="shield-check" class="size-5" /></span>
                <h3 class="mt-4">Privasi dijaga</h3>
                <p class="mt-2 text-text-secondary">Isi catatan dan percakapanmu dienkripsi, dan tinjauan kualitas dilakukan tanpa identitasmu.</p>
            </article>
            <article data-item class="rounded-xl border border-border bg-card p-6">
                <span class="grid size-11 place-items-center rounded-lg bg-brand-soft/50 text-primary"><x-icon name="phone" class="size-5" /></span>
                <h3 class="mt-4">Jalur ke profesional</h3>
                <p class="mt-2 text-text-secondary">Selalu ada arahan dan kontak bantuan bila kamu butuh pendampingan lebih lanjut.</p>
            </article>
        </div>
        <x-disclaimer data-reveal class="mt-8">
            RelaxBoss bukan psikolog atau layanan medis. Untuk penilaian yang tepat, bicarakan dengan tenaga profesional.
            <a href="{{ url('/konsultasi') }}" class="font-medium text-brand-strong underline underline-offset-2">Lihat jalur konsultasi</a>.
        </x-disclaimer>
    </section>

    {{-- UMB: hanya tampil bila terisi (OQ-2, RULE-049) --}}
    @if (filled($umb))
        <section class="mx-auto max-w-3xl px-4 pb-4" aria-labelledby="judul-umb">
            <h2 id="judul-umb">Kaitan akademik</h2>
            <p class="mt-3 text-text-secondary">{{ $umb }}</p>
        </section>
    @endif

    {{-- Bantuan --}}
    <section class="mx-auto max-w-6xl px-4" aria-label="Bantuan">
        <x-help-box data-reveal />
    </section>

    {{-- Penutup --}}
    <section class="mx-auto max-w-6xl px-4 py-16 md:py-20" aria-labelledby="judul-penutup">
        <div data-reveal class="grid items-end gap-8 overflow-hidden rounded-xl bg-neutral-soft px-6 pt-10 md:px-12 md:pt-12 lg:grid-cols-2 lg:pt-0">
            <div class="lg:py-14">
                <h2 id="judul-penutup" class="text-[1.75rem] leading-tight tracking-tight md:text-5xl">Mulai dari satu langkah kecil.</h2>
                <p class="mt-4 max-w-prose text-lg text-text-secondary">Mulai dari asesmen atau catat mood pertamamu hari ini.</p>
                <div class="mt-8"><x-button-link href="{{ url('/daftar') }}" class="min-h-14 px-10 text-lg">Daftar gratis</x-button-link></div>
            </div>
            <x-mood-ribbons class="mx-auto h-auto w-full max-w-md lg:max-w-none" />
        </div>
    </section>
@endsection
