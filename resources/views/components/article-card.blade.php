@props(['item', 'index' => 0])
@php
    // Pastel yang sama dengan kartu Beranda dan Asesmen (Design 2, SCR-001); teks di atasnya --text.
    $tones = ['bg-soft-lavender', 'bg-soft-sky', 'bg-soft-mint', 'bg-soft-peach'];
@endphp
<li data-item>
    <x-ui.card tag="article" :interactive="true" class="h-full overflow-hidden" :flush="true">
        <div class="flex items-center justify-between gap-3 px-5 py-4 {{ $tones[$index % 4] }}">
            <span class="grid size-11 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
                <x-icon name="file-text" class="size-5" />
            </span>
            <x-ui.badge variant="outline">{{ $item['category_label'] }}</x-ui.badge>
        </div>

        <div class="flex flex-1 flex-col gap-2 px-5 pb-5 md:px-6 md:pb-6">
            <x-ui.card.title tag="h2" class="px-0">
                {{-- Satu tautan; after:inset-0 membuat seluruh kartu bisa diklik --}}
                <a href="{{ url('/artikel/'.$item['slug']) }}" class="text-text after:absolute after:inset-0 focus-visible:outline-none">{{ $item['title'] }}</a>
            </x-ui.card.title>
            <x-ui.card.description>{{ $item['excerpt'] }}</x-ui.card.description>

            <div class="mt-auto flex flex-col gap-4 pt-4">
                <x-ui.separator />
                <p class="flex items-center justify-between text-xs text-text-secondary">
                    <time datetime="{{ $item['published_iso'] }}">{{ $item['published_label'] }}</time>
                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary" aria-hidden="true">
                        Baca
                        <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </span>
                </p>
            </div>
        </div>
    </x-ui.card>
</li>
