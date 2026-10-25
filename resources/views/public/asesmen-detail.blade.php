@extends('layouts.public')

@section('title', \Illuminate\Support\Str::limit($assessment['name'], 41, '…').' | Asesmen RelaxBoss')
@section('description', \Illuminate\Support\Str::limit(strip_tags($assessment['description']), 155))

@section('content')
    @php
        // Pastel kartu mengikuti Beranda (Design SCR-001).
        $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
    @endphp

    {{-- 1. Kepala: teks di kiri, kartu ringkasan pastel di kanan (tanpa ilustrasi) --}}
    <section class="bg-background" aria-labelledby="judul-asesmen">
        <div class="mx-auto max-w-6xl px-4 pt-8 pb-8 md:pt-10 md:pb-12">
            <a href="{{ url('/asesmen') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-text-secondary underline-offset-4 transition-colors hover:text-text hover:underline">
                <x-icon name="arrow-left" class="size-4" />
                Semua asesmen
            </a>

            <div class="mt-2 grid items-center gap-8 lg:grid-cols-[3fr_2fr] lg:gap-14">
                <div>
                    <h1 id="judul-asesmen" data-load class="text-[1.75rem] leading-[1.1] font-bold tracking-tight md:text-4xl lg:text-5xl">{{ $assessment['name'] }}</h1>

                    @if ($assessment['is_demo'])
                        <p data-load class="mt-3 w-fit rounded-full bg-soft-peach px-3 py-1 text-xs font-medium text-text">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
                    @endif

                    <p data-load class="mt-4 max-w-prose text-base text-text-secondary">{{ $assessment['description'] }}</p>

                    <div data-load class="mt-6 flex flex-wrap gap-3">
                        <x-button-link href="{{ url('/app/asesmen/'.$assessment['slug']) }}" class="rounded-full! px-6 text-sm">Mulai asesmen</x-button-link>
                        @guest
                            <x-button-link href="{{ url('/daftar') }}" variant="outline" class="rounded-full! border-2! bg-card px-6 text-sm">Daftar gratis</x-button-link>
                        @endguest
                    </div>
                    @guest
                        <p data-load class="mt-3 max-w-prose text-sm text-text-secondary">
                            Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.
                        </p>
                    @endguest
                </div>

                <div data-load="pop" class="w-full max-w-md justify-self-center lg:max-w-none">
                    <div class="rounded-3xl bg-soft-mint p-6 text-text shadow-lift">
                        <span class="grid size-12 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
                            <x-icon name="clipboard-check" class="size-5" />
                        </span>
                        <h2 class="mt-3 text-xl font-bold">Sekilas</h2>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true"><x-icon name="clock" class="size-4" /></span>
                                <div>
                                    <dt class="text-xs text-text-secondary">Waktu pengerjaan</dt>
                                    <dd class="font-semibold">Sekitar {{ $assessment['estimated_minutes'] }} menit</dd>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true"><x-icon name="list-checks" class="size-4" /></span>
                                <div>
                                    <dt class="text-xs text-text-secondary">Jumlah pertanyaan</dt>
                                    <dd class="font-semibold">{{ $assessment['question_count'] }} pertanyaan</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Yang dilihat --}}
    @if (count($assessment['sub_scales']) > 0)
        <section class="bg-background" aria-labelledby="aspek">
            <div class="mx-auto max-w-6xl px-4 pb-10 md:pb-14">
                <div data-reveal class="max-w-xl">
                    <h2 id="aspek" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Yang dilihat</h2>
                    <p class="mt-3 text-base text-text-secondary">Hasilmu ditampilkan terpisah untuk tiap aspek berikut.</p>
                </div>
                <ul data-stagger class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    @foreach ($assessment['sub_scales'] as $subScale)
                        <li data-item>
                            <div data-lift class="flex h-full flex-col gap-3 rounded-2xl p-5 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover {{ $tones[$loop->index % 4] }}">
                                <span class="grid size-10 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
                                    <svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M12 2l1.8 6.5L20 10l-6.2 1.5L12 18l-1.8-6.5L4 10l6.2-1.5z"/></svg>
                                </span>
                                <h3 class="text-base font-semibold">{{ $subScale }}</h3>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- 3. Cara mengerjakan --}}
    <section class="bg-surface-alt" aria-labelledby="cara">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <h2 id="cara" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Cara mengerjakan</h2>
            <div data-reveal class="mt-6 rounded-3xl bg-card p-5 shadow-lift md:p-8">
                @if (filled($assessment['instructions']))
                    <p class="max-w-prose text-base text-text-secondary">{{ $assessment['instructions'] }}</p>
                @endif
                <p class="@if (filled($assessment['instructions'])) mt-3 @endif max-w-prose text-base text-text-secondary">Satu pertanyaan tampil pada satu waktu. Tidak ada jawaban benar atau salah, jawab sesuai keadaanmu.</p>

                @if (count($assessment['options']) > 0)
                    <h3 class="mt-6 text-base font-semibold">Pilihan jawaban</h3>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @foreach ($assessment['options'] as $option)
                            <li class="rounded-full bg-soft-sky px-4 py-1.5 text-sm font-medium text-text">{{ $option }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>

    {{-- 4. Tentang instrumen: hanya bila sumber atau validasi terisi dan bukan Instrumen contoh (RULE-049) --}}
    @if (filled($assessment['source']) || filled($assessment['validated_by']))
        <section class="bg-background" aria-labelledby="sumber">
            <div class="mx-auto max-w-6xl px-4 pt-10 md:pt-14">
                <h2 id="sumber" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Tentang instrumen</h2>
                <dl data-reveal class="mt-4 max-w-prose space-y-3 text-text-secondary">
                    @if (filled($assessment['source']))
                        <div>
                            <dt class="text-sm">Sumber</dt>
                            <dd class="text-base text-text">{{ $assessment['source'] }}</dd>
                        </div>
                    @endif
                    @if (filled($assessment['validated_by']))
                        <div>
                            <dt class="text-sm">Divalidasi oleh</dt>
                            <dd class="text-base text-text">{{ $assessment['validated_by'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>
    @endif

    {{-- 5. Catatan penting --}}
    <section class="bg-background" aria-label="Catatan penting">
        <div class="mx-auto max-w-6xl px-4 pt-10 md:pt-14">
            <div data-reveal class="mx-auto max-w-3xl">
                <div class="flex items-start gap-3 rounded-2xl bg-soft-sky p-4 text-sm text-text" role="note">
                    <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-primary" />
                    <p>Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.</p>
                </div>
                <p class="mt-3">
                    <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline underline-offset-4">
                        Bicara dengan profesional
                        <x-icon name="arrow-right" class="size-4" />
                    </a>
                </p>
            </div>
        </div>
    </section>

    {{-- 6. Penutup untuk tamu --}}
    @guest
        <section class="bg-background" aria-labelledby="judul-penutup">
            <div class="mx-auto max-w-6xl px-4 pt-10 md:pt-14">
                <div data-reveal class="rounded-3xl bg-surface-alt px-5 py-8 md:px-10 md:py-10">
                    <div class="max-w-xl">
                        <h2 id="judul-penutup" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Mulai dari satu langkah kecil.</h2>
                        <p class="mt-3 text-base text-text-secondary">Mulai dari asesmen atau catat mood pertamamu hari ini.</p>
                        <div class="mt-5"><x-button-link href="{{ url('/daftar') }}" class="rounded-full! px-6 text-sm">Daftar gratis</x-button-link></div>
                    </div>
                </div>
            </div>
        </section>
    @endguest

    {{-- 7. Bantuan --}}
    <section class="bg-background" aria-label="Bantuan">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <x-help-box data-reveal />
        </div>
    </section>
@endsection
