@props(['item', 'index' => 0])
@php
    // Pastel yang sama dengan kartu Beranda dan Asesmen (Design 2, SCR-001); teks di atasnya --text.
    $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
@endphp
<li data-item>
    <article data-lift class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-border/60 bg-card text-text shadow-lift transition-shadow duration-300 hover:shadow-lift-hover has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-ring">
        <div class="flex items-center justify-between gap-3 px-5 py-4 {{ $tones[$index % 4] }}">
            <span class="grid size-11 place-items-center rounded-full bg-card/60 text-primary" aria-hidden="true">
                <x-icon name="file-text" class="size-5" />
            </span>
            <span class="rounded-full bg-card/70 px-2.5 py-0.5 text-xs font-medium text-text">{{ $item['category_label'] }}</span>
        </div>

        <div class="flex flex-1 flex-col p-5">
            <h2 class="text-lg leading-snug font-bold">
                {{-- Satu tautan; after:inset-0 membuat seluruh kartu bisa diklik --}}
                <a href="{{ url('/artikel/'.$item['slug']) }}" class="text-text after:absolute after:inset-0 focus-visible:outline-none">{{ $item['title'] }}</a>
            </h2>

            <p class="mt-2 text-sm leading-relaxed text-text-secondary">{{ $item['excerpt'] }}</p>

            <div class="mt-auto pt-5">
                <p class="flex items-center justify-between border-t border-border/60 pt-4 text-xs text-text-secondary">
                    <time datetime="{{ $item['published_iso'] }}">{{ $item['published_label'] }}</time>
                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary" aria-hidden="true">
                        Baca
                        <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </span>
                </p>
            </div>
        </div>
    </article>
</li>
