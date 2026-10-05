@props(['href', 'variant' => 'primary'])
@php
    $base = 'inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary px-6 py-2.5 font-medium transition';
    $classes = $variant === 'outline'
        ? $base.' text-primary hover:bg-primary hover:text-on-primary'
        : $base.' bg-primary text-on-primary hover:brightness-130';
@endphp
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
