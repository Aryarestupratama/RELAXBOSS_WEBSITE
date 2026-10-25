@props(['id', 'title', 'icon', 'tone' => 'yellow'])
@php
    // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
    $circle = match ($tone) {
        'green' => 'bg-mood-green text-text',
        'brand' => 'bg-brand-soft text-text',
        default => 'bg-mood-yellow text-text',
    };
@endphp
<section id="{{ $id }}" aria-labelledby="{{ $id }}-judul" data-reveal
         class="scroll-mt-24 rounded-2xl bg-card p-6 shadow-lift transition-shadow duration-300 hover:shadow-lift-hover md:p-8">
    <div class="flex items-center gap-4">
        <span class="grid size-11 shrink-0 place-items-center rounded-full {{ $circle }}" aria-hidden="true"><x-icon name="{{ $icon }}" class="size-5" /></span>
        <h2 id="{{ $id }}-judul" class="text-xl leading-snug font-bold tracking-tight md:text-2xl">{{ $title }}</h2>
    </div>
    <div class="mt-4 space-y-3 leading-relaxed text-text-secondary [&_a]:font-medium [&_a]:text-brand-strong [&_a]:underline [&_a]:decoration-brand-strong/40 [&_a]:underline-offset-4 [&_a]:transition-colors [&_a:hover]:text-nav-hover [&_a:hover]:decoration-nav-hover">
        {{ $slot }}
    </div>
</section>
