@props(['title', 'intro', 'toc', 'active', 'updated', 'summary' => []])
@php
    // Kerangka halaman hukum Publik (Ketentuan, Privasi): kepala, daftar isi menempel, kartu bagian, tautan ke dokumen satunya.
    // Hanya token (RULE-050). Gerak memakai penanda data-* halaman Publik lain.
    $ease = 'ease-[cubic-bezier(0.2,0.7,0.2,1)]';
    $docs = [
        'ketentuan' => ['label' => 'Ketentuan', 'href' => '/ketentuan', 'icon' => 'file-text'],
        'privasi' => ['label' => 'Privasi', 'href' => '/privasi', 'icon' => 'shield'],
    ];
    $other = $active === 'ketentuan' ? 'privasi' : 'ketentuan';
    // Kata terakhir judul disorot ungu muda, sama seperti judul halaman Tentang.
    $titleWords = explode(' ', $title);
    $titleLast = array_pop($titleWords);
    $titleHead = implode(' ', $titleWords);
    $otherCard = [
        'ketentuan' => ['title' => 'Ketentuan penggunaan', 'text' => 'Aturan pemakaian RelaxBoss, tanggung jawab akun, dan batas layanan.'],
        'privasi' => ['title' => 'Kebijakan privasi', 'text' => 'Data apa yang kami simpan dan bagaimana datamu diperlakukan.'],
    ][$other];
@endphp

{{-- 1. Kepala: kiri = jejak halaman, pilihan dokumen, judul, pengantar, meta; kanan = ringkasan singkat (bila ada) --}}
<section class="relative overflow-hidden bg-background" aria-labelledby="judul-hukum">
    <div class="pointer-events-none absolute -top-28 -right-16 size-80 rounded-full bg-soft-lavender/70 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute top-40 -left-24 size-64 rounded-full bg-soft-sky/50 blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-6xl px-4 pt-6 pb-10 md:pt-8 md:pb-14">
        <div class="grid items-center gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:gap-14">
            <div>
                <nav aria-label="Jejak halaman">
                    <ol class="flex flex-wrap items-center gap-x-2 text-sm text-text-secondary">
                        <li><a href="{{ url('/') }}" class="inline-flex min-h-11 items-center underline-offset-4 transition-colors hover:text-nav-hover hover:underline">Beranda</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page" class="text-text">{{ $title }}</li>
                    </ol>
                </nav>

                <nav data-load aria-label="Dokumen hukum" class="mt-1">
                    <ul class="inline-flex items-center gap-1 rounded-full border border-border bg-card/70 p-1 shadow-card">
                        @foreach ($docs as $key => $doc)
                            <li>
                                <a href="{{ url($doc['href']) }}"
                                   @if ($key === $active) aria-current="page" @endif
                                   class="relative inline-flex h-8 items-center gap-1.5 rounded-full px-3.5 text-[0.8125rem] font-medium transition-all duration-300 {{ $ease }} after:absolute after:inset-x-0 after:-inset-y-1.5 {{ $key === $active ? 'bg-soft-lavender text-text shadow-lift' : 'text-text-secondary hover:bg-card/60 hover:text-nav-hover' }}">
                                    <x-icon name="{{ $doc['icon'] }}" class="size-3.5" />
                                    {{ $doc['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <h1 id="judul-hukum" data-load class="mt-5 max-w-3xl text-[2rem] leading-[1.1] font-bold tracking-tight md:text-5xl lg:text-[3.25rem]">
                    {{ $titleHead }}
                    <span class="rounded-xl bg-soft-lavender px-2 box-decoration-clone">{{ $titleLast }}</span>
                </h1>
                <p data-load class="mt-5 max-w-prose text-base leading-relaxed text-text-secondary md:text-lg">{{ $intro }}</p>

                <ul data-load class="mt-6 flex flex-wrap items-center gap-2 text-xs text-text" aria-label="Info dokumen">
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-card px-3 py-1.5 shadow-card"><x-icon name="clock" class="size-4 text-primary" />Diperbarui {{ $updated }}</li>
                    <li class="inline-flex items-center gap-1.5 rounded-full border border-border/60 bg-card px-3 py-1.5 shadow-card"><x-icon name="list-checks" class="size-4 text-primary" />{{ count($toc) }} bagian</li>
                </ul>
            </div>

            @if (count($summary) > 0)
                <aside data-load aria-labelledby="ringkasan-hukum" class="rounded-3xl border border-border/60 bg-card p-6 shadow-lift md:p-7">
                    <p id="ringkasan-hukum" class="text-xs font-semibold tracking-[0.14em] text-primary uppercase">Singkatnya</p>
                    <ul class="mt-4 divide-y divide-border/60">
                        @foreach ($summary as $point)
                            <li class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-soft-lavender text-text" aria-hidden="true"><x-icon name="{{ $point['icon'] }}" class="size-5" /></span>
                                <div>
                                    <p class="text-base leading-snug font-semibold text-text">{{ $point['title'] }}</p>
                                    <p class="mt-1 text-sm leading-relaxed text-text-secondary">{{ $point['text'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </aside>
            @endif
        </div>
    </div>
</section>

{{-- 2. Isi: daftar isi menempel (>= 1024px), chip tautan di HP, dan satu kartu per bagian --}}
<section class="bg-surface-alt" aria-label="Isi dokumen">
    <div class="mx-auto max-w-6xl px-4 py-10 md:py-14">
        <nav aria-label="Lompat ke bagian" class="-mx-4 mb-6 overflow-x-auto px-4 lg:hidden">
            <ul class="flex w-max gap-2 pb-1">
                @foreach ($toc as $id => $label)
                    <li><a href="#{{ $id }}" class="inline-flex h-9 items-center rounded-full bg-card px-3.5 text-xs font-medium whitespace-nowrap text-text shadow-card transition-all duration-300 {{ $ease }} hover:-translate-y-px hover:text-nav-hover hover:shadow-lift">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <div class="grid items-start gap-8 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-12">
            <nav aria-label="Di halaman ini" class="hidden lg:sticky lg:top-24 lg:block">
                <p class="mb-3 text-xs font-semibold tracking-[0.14em] text-text-secondary uppercase">Di halaman ini</p>
                <ul class="border-l border-border">
                    @foreach ($toc as $id => $label)
                        <li>
                            <a href="#{{ $id }}" data-toc-link
                               class="-ml-px flex gap-3 border-l-2 border-transparent py-2 pl-4 text-[0.8125rem] leading-snug text-text-secondary transition-colors duration-300 hover:text-nav-hover aria-[current=true]:border-primary aria-[current=true]:font-medium aria-[current=true]:text-text">
                                <span class="tabular-nums" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span>{{ $label }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Satu lembar putih untuk seluruh isi; tiap bagian dipisah garis tipis --}}
            <div class="max-w-3xl divide-y divide-border/60 overflow-hidden rounded-3xl border border-border/60 bg-card shadow-lift">
                {{ $slot }}
            </div>
        </div>
    </div>
</section>

{{-- 3. Penutup: catatan (bila ada) dan tautan ke dokumen satunya --}}
<section class="bg-background" aria-label="Dokumen terkait">
    <div class="mx-auto max-w-6xl space-y-5 px-4 py-10 md:py-14">
        @isset($after)
            <div data-reveal class="max-w-3xl">
                {{ $after }}
            </div>
        @endisset

        <a href="{{ url($docs[$other]['href']) }}" data-reveal data-lift
           class="group flex max-w-3xl items-center gap-4 rounded-3xl bg-card p-5 text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover md:p-6">
            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true"><x-icon name="{{ $docs[$other]['icon'] }}" class="size-5" /></span>
            <span class="flex-1">
                <span class="block text-xl font-bold">{{ $otherCard['title'] }}</span>
                <span class="mt-1 block text-sm leading-relaxed text-text-secondary">{{ $otherCard['text'] }}</span>
            </span>
            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-primary text-on-primary transition-all duration-300 {{ $ease }} group-hover:translate-x-1 group-hover:bg-nav-hover" aria-hidden="true"><x-icon name="arrow-right" class="size-5" /></span>
        </a>
    </div>
</section>
