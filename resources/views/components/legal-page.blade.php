@props(['title', 'intro', 'toc', 'active', 'updated'])
@php
    // Kerangka halaman hukum Publik (Ketentuan, Privasi): kepala, daftar isi menempel, kartu bagian, tautan ke dokumen satunya.
    // Hanya token (RULE-050). Gerak memakai penanda data-* halaman Publik lain.
    $ease = 'ease-[cubic-bezier(0.2,0.7,0.2,1)]';
    $docs = [
        'ketentuan' => ['label' => 'Ketentuan', 'href' => '/ketentuan', 'icon' => 'file-text'],
        'privasi' => ['label' => 'Privasi', 'href' => '/privasi', 'icon' => 'shield'],
    ];
    $other = $active === 'ketentuan' ? 'privasi' : 'ketentuan';
    $otherCard = [
        'ketentuan' => ['title' => 'Ketentuan penggunaan', 'text' => 'Aturan pemakaian RelaxBoss, tanggung jawab akun, dan batas layanan.'],
        'privasi' => ['title' => 'Kebijakan privasi', 'text' => 'Data apa yang kami simpan dan bagaimana datamu diperlakukan.'],
    ][$other];
@endphp

{{-- 1. Kepala: jejak halaman, pilihan dokumen, judul, pengantar, meta --}}
<section class="bg-background" aria-labelledby="judul-hukum">
    <div class="mx-auto max-w-6xl px-4 pt-6 pb-8 md:pt-8 md:pb-10">
        <nav aria-label="Jejak halaman">
            <ol class="flex flex-wrap items-center gap-x-2 text-sm text-text-secondary">
                <li><a href="{{ url('/') }}" class="inline-flex min-h-11 items-center underline-offset-4 transition-colors hover:text-nav-hover hover:underline">Beranda</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-text">{{ $title }}</li>
            </ol>
        </nav>

        <nav data-load aria-label="Dokumen hukum" class="mt-1">
            <ul class="inline-flex items-center gap-1 rounded-full border border-border bg-surface-alt p-1">
                @foreach ($docs as $key => $doc)
                    <li>
                        <a href="{{ url($doc['href']) }}"
                           @if ($key === $active) aria-current="page" @endif
                           class="relative inline-flex h-8 items-center gap-1.5 rounded-full px-3.5 text-[0.8125rem] font-medium transition-all duration-300 {{ $ease }} after:absolute after:inset-x-0 after:-inset-y-1.5 {{ $key === $active ? 'bg-card text-text shadow-lift' : 'text-text-secondary hover:bg-card/60 hover:text-nav-hover' }}">
                            <x-icon name="{{ $doc['icon'] }}" class="size-3.5" />
                            {{ $doc['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <h1 id="judul-hukum" data-load class="mt-4 max-w-3xl text-[1.75rem] leading-[1.15] font-bold tracking-tight md:text-4xl lg:text-[2.75rem]">{{ $title }}</h1>
        <p data-load class="mt-4 max-w-prose text-base text-text-secondary md:text-lg">{{ $intro }}</p>

        <ul data-load class="mt-5 flex flex-wrap items-center gap-2 text-xs text-text" aria-label="Info dokumen">
            <li class="inline-flex items-center gap-1.5 rounded-full bg-surface-alt px-3 py-1.5"><x-icon name="clock" class="size-4 text-primary" />Diperbarui {{ $updated }}</li>
            <li class="inline-flex items-center gap-1.5 rounded-full bg-surface-alt px-3 py-1.5"><x-icon name="list-checks" class="size-4 text-primary" />{{ count($toc) }} bagian</li>
        </ul>
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

        <div class="grid items-start gap-8 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-10">
            <nav aria-label="Di halaman ini" class="hidden rounded-2xl bg-card p-3 shadow-lift lg:sticky lg:top-24 lg:block">
                <p class="px-3 pt-1 pb-2 text-sm font-semibold text-text">Di halaman ini</p>
                <ul class="space-y-0.5">
                    @foreach ($toc as $id => $label)
                        <li>
                            <a href="#{{ $id }}" data-toc-link
                               class="block rounded-lg px-3 py-2 text-[0.8125rem] leading-snug text-text-secondary transition-colors duration-300 hover:bg-surface-alt hover:text-nav-hover aria-[current=true]:bg-soft-lavender aria-[current=true]:font-medium aria-[current=true]:text-text">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="max-w-3xl space-y-5">
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
