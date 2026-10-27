@props(['id', 'title', 'icon', 'tone' => 'yellow'])
@php
    // Lingkaran ikon selalu ungu muda (TASK-028AB); `tone` dipertahankan agar pemanggil lama tidak berubah.
    $circle = 'bg-soft-lavender text-text';
@endphp
<x-ui.card tag="section" id="{{ $id }}" aria-labelledby="{{ $id }}-judul" data-reveal class="scroll-mt-24 p-6 md:p-8" :flush="true">
    <div class="flex items-center gap-4">
        <span class="grid size-11 shrink-0 place-items-center rounded-full {{ $circle }}" aria-hidden="true"><x-icon name="{{ $icon }}" class="size-5" /></span>
        <h2 id="{{ $id }}-judul" class="text-xl leading-snug font-bold tracking-tight md:text-2xl">{{ $title }}</h2>
    </div>
    <div class="mt-4 space-y-3 leading-relaxed text-text-secondary [&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:decoration-primary/40 [&_a]:underline-offset-4 [&_a]:transition-colors [&_a:hover]:text-nav-hover [&_a:hover]:decoration-nav-hover">
        {{ $slot }}
    </div>
</x-ui.card>
