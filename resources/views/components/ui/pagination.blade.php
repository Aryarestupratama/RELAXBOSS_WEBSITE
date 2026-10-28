@props(['paginator', 'label' => 'Halaman'])
@php
    // Paginasi bernomor bergaya shadcn untuk Blade (Pagination): Sebelumnya, nomor halaman, Berikutnya.
    // Warna hanya dari token (RULE-050/052). Halaman aktif memakai aria-current, bukan warna saja (RULE-052).
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $items = \App\Support\PageWindow::make($current, $last);
    $ease = 'transition duration-200 select-none';
    $number = 'inline-flex size-11 shrink-0 items-center justify-center rounded-full border-[1.5px] text-sm font-medium '.$ease;
    $idle = $number.' border-border/60 bg-card text-text hover:bg-soft-lavender';
    $active = $number.' border-primary bg-primary text-on-primary';
    $edge = 'inline-flex min-h-11 min-w-11 items-center justify-center gap-1.5 rounded-full border-[1.5px] border-primary bg-transparent px-3 text-sm font-medium text-primary sm:px-4 '.$ease;
    $edgeLive = $edge.' hover:border-nav-hover hover:bg-nav-hover hover:text-on-primary';
    $edgeOff = $edge.' pointer-events-none opacity-50';
@endphp
@if ($last > 1)
    <nav aria-label="{{ $label }}" {{ $attributes->class('flex flex-wrap items-center justify-center gap-2') }}>
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="{{ $edgeOff }}">
                <x-icon name="chevron-left" class="size-4" />
                <span class="sr-only sm:not-sr-only">Sebelumnya</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" data-press class="{{ $edgeLive }}">
                <x-icon name="chevron-left" class="size-4" />
                <span class="sr-only sm:not-sr-only">Sebelumnya</span>
            </a>
        @endif

        <ul class="flex flex-wrap items-center justify-center gap-2">
            @foreach ($items as $item)
                <li>
                    @if ($item === null)
                        <span class="inline-flex size-11 items-center justify-center text-sm text-text-secondary" aria-hidden="true">&hellip;</span>
                    @elseif ($item === $current)
                        <a href="{{ $paginator->url($item) }}" aria-current="page" aria-label="Halaman {{ $item }}, halaman saat ini" class="{{ $active }}">{{ $item }}</a>
                    @else
                        <a href="{{ $paginator->url($item) }}" aria-label="Ke halaman {{ $item }}" class="{{ $idle }}">{{ $item }}</a>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" data-press class="{{ $edgeLive }}">
                <span class="sr-only sm:not-sr-only">Berikutnya</span>
                <x-icon name="chevron-right" class="size-4" />
            </a>
        @else
            <span aria-disabled="true" class="{{ $edgeOff }}">
                <span class="sr-only sm:not-sr-only">Berikutnya</span>
                <x-icon name="chevron-right" class="size-4" />
            </span>
        @endif
    </nav>
@endif
