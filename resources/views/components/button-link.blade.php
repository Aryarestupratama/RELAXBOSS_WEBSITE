@props(['href', 'variant' => 'primary'])
{{-- Pembungkus lama: semua tombol-tautan kini memakai x-ui.button (gaya shadcn). --}}
<x-ui.button :href="$href" :variant="$variant === 'outline' ? 'outline' : 'default'" {{ $attributes }}>{{ $slot }}</x-ui.button>
