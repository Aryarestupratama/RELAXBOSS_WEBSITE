@props(['icon', 'title', 'message' => null])
{{-- Empty state sederhana (padanan CMP-010 di React): ikon Lucide di tengah lingkaran konsentris, judul, pesan opsional, aksi di slot. --}}
<div {{ $attributes->class(['flex flex-col items-center text-center']) }}>
    <div class="relative grid size-40 place-items-center" aria-hidden="true">
        <span class="absolute size-40 rounded-full border border-primary/5"></span>
        <span class="absolute size-30 rounded-full border border-primary/10"></span>
        <span class="grid size-20 place-items-center rounded-full border border-primary/15 bg-soft-lavender text-primary">
            <x-icon :name="$icon" class="size-8" />
        </span>
    </div>
    <p class="mt-2 text-lg font-semibold text-text">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-sm text-sm text-text-secondary">{{ $message }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
