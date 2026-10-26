@props(['variant' => 'default', 'icon' => null, 'title' => null, 'compact' => false])
@php
    // Alert bergaya shadcn. `crisis` memakai token krisis; dipakai untuk kotak bantuan (RULE-052).
    $style = match ($variant) {
        'crisis' => 'border-crisis-text/30 bg-crisis-bg text-crisis-text',
        'info' => 'border-transparent bg-soft-lavender text-text',
        default => 'border-border/60 bg-surface-alt text-text',
    };
@endphp
<div data-slot="alert" {{ $attributes->merge(['role' => 'note'])->class(['flex items-start gap-3 rounded-2xl border', $compact ? 'p-3' : 'p-4 md:p-5', $style]) }}>
    @if ($icon)
        <span class="mt-0.5 shrink-0" aria-hidden="true"><x-icon :name="$icon" class="size-5" /></span>
    @endif
    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="text-sm leading-relaxed">{{ $slot }}</div>
    </div>
</div>
