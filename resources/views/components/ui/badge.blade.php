@props(['variant' => 'secondary', 'tone' => null])
@php
    // Badge bergaya shadcn. Bawaan: mist + teks navy + garis tepi tipis (tidak pernah isian aksen). `soft` + `tone` memakai pastel; teks selalu --text (RULE-052).
    $style = match ($variant) {
        'default' => 'bg-primary text-on-primary',
        'outline' => 'border border-border bg-card text-text',
        'soft' => match ($tone) {
            'sky' => 'bg-soft-sky text-text',
            'mint' => 'bg-soft-mint text-text',
            'peach' => 'bg-soft-peach text-text',
            default => 'bg-soft-lavender text-text',
        },
        default => 'border border-primary/15 bg-surface-alt text-primary',
    };
@endphp
<span data-slot="badge" {{ $attributes->class(['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap', $style]) }}>{{ $slot }}</span>
