@extends('layouts.public')

@section('title', 'Tentang RelaxBoss')
@section('description', 'RelaxBoss membantu mahasiswa mengenali dan mengelola stres lewat asesmen, mood tracker, dan RelaxMate, teman bicara berbasis AI.')

@section('content')
    {{-- DRAF SEMENTARA: teks dibaca founder sebelum launch (TASK-030). Klaim kampus hanya dari config (RULE-049). --}}
    @php
        $collaboration = (string) ($about['collaboration'] ?? '');
        $award = (array) ($about['award'] ?? []);
        $awardText = filled($award['title'] ?? null)
            ? trim((string) preg_replace('/\s+/', ' ', $award['title'].' '.(filled($award['event'] ?? null) ? 'di '.$award['event'] : '').' '.($award['year'] ?? '')))
            : '';
        $supervisors = array_values(array_filter((array) ($about['supervisors'] ?? []), 'filled'));
        $campusCards = array_values(array_filter([
            filled($collaboration) ? ['icon' => 'users', 'tone' => 'sky', 'title' => 'Kolaborasi dua fakultas', 'text' => $collaboration] : null,
            filled($awardText) ? ['icon' => 'graduation-cap', 'tone' => 'peach', 'title' => 'Penghargaan', 'text' => $awardText.'.'] : null,
            count($supervisors) > 0 ? ['icon' => 'shield-check', 'tone' => 'mint', 'title' => 'Didampingi dan dipantau', 'list' => $supervisors] : null,
        ]));
    @endphp

    {{-- 1. Hero: teks di kiri, logo di kanan --}}
    <section class="bg-background" aria-labelledby="judul-tentang">
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 pt-10 pb-10 md:pt-14 md:pb-14 lg:grid-cols-[3fr_2fr] lg:gap-14">
            <div>
                <h1 id="judul-tentang" data-load class="text-[1.875rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">
                    Tentang <span class="rounded-xl bg-soft-lavender px-2 box-decoration-clone">RelaxBoss</span>
                </h1>
                <p data-load class="mt-4 max-w-lg text-base text-text-secondary">
                    Teman yang sederhana, gratis, dan ramah untuk membantu mahasiswa mengenali dan mengelola stres lebih awal.
                </p>
            </div>
            <div data-load="pop" class="flex justify-center">
                <img src="{{ asset('images/logo-relaxboss.png') }}" width="192" height="192" fetchpriority="high" alt="Logo RelaxBoss" class="size-32 rounded-3xl shadow-lift-hover md:size-40">
            </div>
        </div>
    </section>

    {{-- 2. Cerita awal dan visi --}}
    <section class="bg-surface-alt" aria-labelledby="judul-cerita">
        <div class="mx-auto grid max-w-6xl items-center gap-8 px-4 py-10 md:py-14 lg:grid-cols-[7fr_5fr] lg:gap-14">
            <div data-reveal="left">
                <h2 id="judul-cerita" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Berawal dari burnout</h2>
                <p class="mt-3 max-w-prose text-base text-text-secondary">
                    Ide RelaxBoss muncul dari pengalaman pengembangnya sendiri: mengalami burnout saat kuliah. Dari situ lahir pertanyaan sederhana,
                    bagaimana kalau mahasiswa punya tempat untuk mengenali stres lebih awal, tanpa biaya dan tanpa rasa canggung?
                </p>
                <h3 class="mt-6 text-lg font-bold">Visi</h3>
                <p class="mt-2 max-w-prose text-base text-text-secondary">
                    Membantu mahasiswa mengenali dan mengelola stres lebih awal, dengan cara yang sederhana, gratis, dan ramah.
                    Kami percaya kamu yang memegang kendali atas kondisimu, bukan sebaliknya.
                </p>
            </div>

            <figure data-reveal="right" class="flex min-h-40 flex-col justify-center rounded-3xl bg-card p-6 text-text shadow-lift md:p-8">
                <blockquote class="text-xl leading-snug font-bold md:text-2xl">
                    &ldquo;Anda yang mengendalikan stres, bukan sebaliknya.&rdquo;
                </blockquote>
            </figure>
        </div>
    </section>

    {{-- 3. Dibangun bersama kampus (hanya bila config terisi, RULE-049) --}}
    @if (count($campusCards) > 0)
        <section class="bg-background" aria-labelledby="judul-kampus">
            <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
                <div data-reveal class="mx-auto max-w-xl text-center">
                    <h2 id="judul-kampus" class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Dibangun bersama kampus</h2>
                    <p class="mt-3 text-base text-text-secondary">RelaxBoss dikembangkan di lingkungan Universitas Mercu Buana.</p>
                </div>
                <div data-stagger class="mt-8 grid gap-4 md:grid-cols-3">
                    @foreach ($campusCards as $card)
                        <x-ui.card tag="article" :tone="$card['tone']" radius="rounded-3xl" data-item data-lift class="gap-2! px-5 transition-shadow duration-300 hover:shadow-lift-hover">
                            <x-ui.card.header class="px-0!">
                                <span class="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="{{ $card['icon'] }}" class="size-5" /></span>
                                <x-ui.card.title class="mt-1 text-xl">{{ $card['title'] }}</x-ui.card.title>
                            </x-ui.card.header>
                            <x-ui.card.content class="px-0!">
                                @isset($card['list'])
                                    <p class="text-[0.8125rem] leading-relaxed text-text-secondary">Dalam pengembangannya, RelaxBoss didampingi dan dipantau oleh:</p>
                                    <ul class="mt-2 space-y-1.5 text-sm font-medium">
                                        @foreach ($card['list'] as $role)
                                            <li class="flex items-start gap-2"><span class="mt-2 size-1.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>{{ $role }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-[0.8125rem] leading-relaxed text-text-secondary">{{ $card['text'] }}</p>
                                @endisset
                            </x-ui.card.content>
                        </x-ui.card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 4. Yang bisa kamu lakukan --}}
    <section class="bg-surface-alt" aria-labelledby="fitur">
        <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
            <h2 id="fitur" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Yang bisa kamu lakukan</h2>
            <div data-stagger class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'clipboard-check', 'tone' => 'peach', 'name' => 'Asesmen', 'text' => 'Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Hasilnya bukan diagnosis.'],
                    ['icon' => 'smile', 'tone' => 'sky', 'name' => 'Mood Tracker', 'text' => 'Catat perasaanmu dari hari ke hari dan lihat polanya.'],
                    ['icon' => 'message-circle', 'tone' => 'mint', 'name' => 'RelaxMate', 'text' => 'Teman bicara berbasis AI untuk bercerita kapan saja. Bukan psikolog atau layanan medis.'],
                ] as $feature)
                    <x-ui.card tag="article" :tone="$feature['tone']" radius="rounded-3xl" data-item data-lift class="gap-2! px-5 transition-shadow duration-300 hover:shadow-lift-hover">
                        <x-ui.card.header class="px-0!">
                            <span class="grid size-12 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="{{ $feature['icon'] }}" class="size-5" /></span>
                            <x-ui.card.title class="mt-1 text-xl">{{ $feature['name'] }}</x-ui.card.title>
                            <x-ui.card.description class="text-[0.8125rem]">{{ $feature['text'] }}</x-ui.card.description>
                        </x-ui.card.header>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. Tentang nama: tipografi saja, tanpa kartu. Relax di kiri atas, Boss di kanan bawah. --}}
    <section class="bg-background" aria-labelledby="nama">
        <div class="mx-auto max-w-5xl px-4 py-16 md:py-24">
            <h2 id="nama" data-reveal class="text-xs font-semibold tracking-[0.3em] text-text-secondary uppercase">Tentang nama</h2>

            <div class="mt-10 md:mt-14">
                <div data-reveal="left" class="max-w-md">
                    <p class="text-xs font-medium tracking-[0.3em] text-text-secondary" aria-hidden="true">01</p>
                    <h3 class="mt-3 text-6xl leading-none font-light tracking-tight text-text md:text-8xl">Relax</h3>
                    <p class="mt-5 text-base leading-relaxed text-text-secondary md:text-lg">Ajakan untuk tenang.</p>
                </div>

                <div class="my-10 border-t border-border md:my-14" aria-hidden="true"></div>

                <div data-reveal="right" class="ml-auto max-w-md text-right">
                    <p class="text-xs font-medium tracking-[0.3em] text-text-secondary" aria-hidden="true">02</p>
                    <h3 class="mt-3 text-6xl leading-none font-light tracking-tight text-primary md:text-8xl">Boss</h3>
                    <p class="mt-5 text-base leading-relaxed text-text-secondary md:text-lg">Pengingat bahwa kamulah yang memegang kendali atas stresmu.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. Ruang aman, ditutup catatan sebaris (pola halaman Artikel dan Asesmen) --}}
    <section class="bg-surface-alt" aria-labelledby="judul-aman">
        <div class="mx-auto max-w-6xl px-4 pt-10 pb-14 md:pt-14 md:pb-20">
            <h2 id="judul-aman" data-reveal class="text-2xl leading-tight font-bold tracking-tight md:text-[2rem]">Ruang aman untuk mahasiswa</h2>
            <div data-stagger class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'clipboard-check', 'tone' => 'peach', 'name' => 'Bukan diagnosis', 'text' => 'Hasil asesmen adalah gambaran awal untuk membantumu mengenali kondisi, bukan penilaian medis.'],
                    ['icon' => 'shield-check', 'tone' => 'sky', 'name' => 'Privasi dijaga', 'text' => 'Isi catatan dan percakapanmu dienkripsi, dan tinjauan kualitas dilakukan tanpa identitasmu.'],
                    ['icon' => 'message-circle', 'tone' => 'mint', 'name' => 'Jalur ke profesional', 'text' => 'Selalu ada arahan dan kontak bantuan bila kamu butuh pendampingan lebih lanjut.'],
                ] as $item)
                    <x-ui.card tag="article" data-item data-lift class="gap-0! px-5 transition-shadow duration-300 hover:shadow-lift-hover">
                        <x-ui.card.header class="px-0!">
                            <span class="grid size-11 place-items-center rounded-full text-primary {{ ['peach' => 'bg-soft-peach', 'sky' => 'bg-soft-sky', 'mint' => 'bg-soft-mint'][$item['tone']] }}" aria-hidden="true"><x-icon name="{{ $item['icon'] }}" class="size-5" /></span>
                            <x-ui.card.title class="mt-3 text-lg">{{ $item['name'] }}</x-ui.card.title>
                            <x-ui.card.description class="mt-1 leading-relaxed">{{ $item['text'] }}</x-ui.card.description>
                        </x-ui.card.header>
                    </x-ui.card>
                @endforeach
            </div>

            <x-inline-note data-reveal class="mt-8">RelaxBoss bukan psikolog atau layanan medis, jadi untuk penilaian yang tepat <a href="{{ url('/konsultasi') }}">bicarakan dengan profesional</a>.</x-inline-note>
        </div>
    </section>
@endsection
