@props(['item', 'index' => 0])
@php
    // Urutan warna kuning, hijau, ungu mengikuti kartu fitur Beranda (Design 7, SCR-001).
    $circles = ['bg-mood-yellow text-text', 'bg-mood-green text-text', 'bg-brand-soft text-text'];
@endphp
<li data-item data-lift class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-card transition-shadow duration-300 hover:shadow-popover lg:p-6">
    <span class="grid size-14 place-items-center rounded-full {{ $circles[$index % 3] }}" aria-hidden="true">
        <x-icon name="file-text" class="size-7" />
    </span>

    <p class="mt-4"><span class="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text">{{ $item['category_label'] }}</span></p>

    <h2 class="mt-3 text-xl font-semibold">
        <a href="{{ url('/artikel/'.$item['slug']) }}" class="text-text underline-offset-4 hover:text-brand-strong hover:underline">{{ $item['title'] }}</a>
    </h2>

    <p class="mt-2 text-text-secondary">{{ $item['excerpt'] }}</p>

    <p class="mt-auto pt-5 text-sm text-text-secondary">
        <time datetime="{{ $item['published_iso'] }}">{{ $item['published_label'] }}</time>
    </p>
</li>
