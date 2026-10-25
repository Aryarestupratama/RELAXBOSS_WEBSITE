@props(['id', 'label' => 'Catatan penting', 'align' => 'center'])
@php
    // Posisi kotak: `end` menempel di tepi kanan tombol (aman untuk elemen di sisi kanan layar sempit).
    $position = match ($align) {
        'start' => 'left-0',
        'end' => 'right-0',
        default => 'left-1/2 -translate-x-1/2',
    };
@endphp
{{-- Tooltip "i": muncul saat hover, fokus keyboard, atau ketuk (public.ts, initInfoTips); Esc menutup. --}}
<span data-info-tip class="group relative inline-flex">
    <button type="button" aria-label="{{ $label }}" aria-describedby="{{ $id }}"
            class="grid size-11 place-items-center rounded-full text-primary transition-colors hover:bg-surface-alt">
        <x-icon name="info" class="size-5" />
    </button>
    <span id="{{ $id }}" role="tooltip"
          class="invisible absolute top-full z-30 w-[min(18rem,calc(100vw-2rem))] pt-1 opacity-0 transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100 group-data-[open]:visible group-data-[open]:opacity-100 group-data-[dismissed]:invisible! group-data-[dismissed]:opacity-0! {{ $position }}">
        <span class="block rounded-xl bg-primary px-4 py-3 text-left text-sm leading-relaxed text-on-primary shadow-popover">{{ $slot }}</span>
    </span>
</span>
