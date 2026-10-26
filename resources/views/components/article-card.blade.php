@props(['item'])
{{-- Kartu putih standar (pola Asesmen): hanya lingkaran ikon dan kategori yang berwarna ungu muda (--soft-lavender). --}}
<li data-item>
    <x-ui.card tag="article" :interactive="true" class="h-full">
        <x-ui.card.header>
            <div class="mb-1 flex items-center justify-between gap-3">
                <span class="grid size-11 place-items-center rounded-full bg-soft-lavender text-primary" aria-hidden="true">
                    <x-icon name="file-text" class="size-5" />
                </span>
                <x-ui.badge variant="soft" tone="lavender">{{ $item['category_label'] }}</x-ui.badge>
            </div>
            <x-ui.card.title tag="h2">
                {{-- Satu tautan; after:inset-0 membuat seluruh kartu bisa diklik --}}
                <a href="{{ url('/artikel/'.$item['slug']) }}" class="text-text after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none">{{ $item['title'] }}</a>
            </x-ui.card.title>
            <x-ui.card.description>{{ $item['excerpt'] }}</x-ui.card.description>
        </x-ui.card.header>

        <x-ui.card.footer class="flex-col gap-4 pt-2">
            <x-ui.separator />
            <p class="flex items-center justify-between text-xs text-text-secondary">
                <time datetime="{{ $item['published_iso'] }}">{{ $item['published_label'] }}</time>
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary" aria-hidden="true">
                    Baca
                    <x-icon name="arrow-right" class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                </span>
            </p>
        </x-ui.card.footer>
    </x-ui.card>
</li>
