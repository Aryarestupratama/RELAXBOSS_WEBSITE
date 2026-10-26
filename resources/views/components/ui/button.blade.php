@props(['href' => null, 'variant' => 'default', 'size' => 'default'])
@php
    // Tombol bergaya shadcn untuk Blade. Hover bernavy memakai --nav-hover (RULE-052); target sentuh 44px.
    $variantClass = match ($variant) {
        'outline' => 'border-primary bg-transparent text-primary hover:bg-primary hover:text-on-primary',
        'secondary' => 'border-transparent bg-neutral-soft text-text hover:bg-soft-lavender',
        'ghost' => 'border-transparent text-text hover:bg-neutral-soft',
        'link' => 'border-transparent text-brand-strong underline underline-offset-4 hover:text-nav-hover',
        default => 'border-primary bg-primary text-on-primary hover:border-nav-hover hover:bg-nav-hover hover:shadow-card',
    };
    $sizeClass = $size === 'sm' ? 'min-h-11 px-4 py-2 text-sm' : 'min-h-11 px-6 py-2.5';
    $classes = ['inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border-[1.5px] font-medium whitespace-nowrap transition duration-200 select-none disabled:pointer-events-none disabled:opacity-50', $variantClass, $sizeClass];
@endphp
@if ($href)
    <a href="{{ $href }}" data-slot="button" data-press {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button data-slot="button" data-press {{ $attributes->merge(['type' => 'button'])->class($classes) }}>{{ $slot }}</button>
@endif
