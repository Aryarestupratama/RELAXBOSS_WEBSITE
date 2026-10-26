@props(['tag' => 'div', 'tone' => null, 'interactive' => false, 'flush' => false, 'radius' => 'rounded-2xl'])
@php
    // Kartu bergaya shadcn (Card). Warna hanya dari token (RULE-050/052).
    // `interactive`: seluruh kartu bisa diklik lewat satu tautan `after:absolute after:inset-0` di judul.
    // Keputusan founder (TASK-028AB): semua kartu putih; `tone` dipertahankan agar pemanggil lama tidak berubah.
    $surface = 'bg-card';
@endphp
<{{ $tag }} data-slot="card" @if ($interactive) data-lift @endif
    {{ $attributes->class([
        'flex flex-col '.$radius.' border border-border/60 text-text shadow-lift',
        'gap-4 py-5 md:py-6' => ! $flush,
        $surface,
        'group relative transition-shadow duration-300 hover:shadow-lift-hover has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring' => $interactive,
    ]) }}>
    {{ $slot }}
</{{ $tag }}>
