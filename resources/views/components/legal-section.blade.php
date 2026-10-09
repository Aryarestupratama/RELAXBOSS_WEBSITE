@props(['id', 'title', 'icon', 'tone' => 'yellow'])
@php
    // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
    $circle = match ($tone) {
        'green' => 'bg-mood-green text-text',
        'brand' => 'bg-brand text-on-primary',
        default => 'bg-mood-yellow text-text',
    };
@endphp
<section id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="scroll-mt-24 py-10 first:pt-0 last:pb-0">
    <div class="flex items-center gap-4">
        <span class="grid size-10 shrink-0 place-items-center rounded-full {{ $circle }}" aria-hidden="true"><x-icon name="{{ $icon }}" class="size-5" /></span>
        <h2 id="{{ $id }}-judul" class="text-2xl">{{ $title }}</h2>
    </div>
    <div class="mt-4 text-text-secondary">
        {{ $slot }}
    </div>
</section>
