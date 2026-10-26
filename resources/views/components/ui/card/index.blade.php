@props(['tag' => 'div', 'tone' => null, 'interactive' => false, 'flush' => false])
@php
    // Kartu bergaya shadcn (Card). Warna hanya dari token; teks di atas pastel selalu --text (RULE-052).
    // `interactive`: seluruh kartu bisa diklik lewat satu tautan `after:absolute after:inset-0` di judul.
    $surface = match ($tone) {
        'lavender' => 'bg-soft-lavender',
        'sky' => 'bg-soft-sky',
        'mint' => 'bg-soft-mint',
        'peach' => 'bg-soft-peach',
        'muted' => 'bg-surface-alt',
        default => 'bg-card',
    };
@endphp
<{{ $tag }} data-slot="card" @if ($interactive) data-lift @endif
    {{ $attributes->class([
        'flex flex-col rounded-2xl border border-border/60 text-text shadow-lift',
        'gap-4 py-5 md:py-6' => ! $flush,
        $surface,
        'group relative transition-shadow duration-300 hover:shadow-lift-hover has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring' => $interactive,
    ]) }}>
    {{ $slot }}
</{{ $tag }}>
