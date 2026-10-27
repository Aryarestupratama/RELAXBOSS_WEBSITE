@props(['icon' => 'info'])
{{-- Catatan satu kalimat selebar kontennya (padanan React: Components/shared/InlineNote). Tautan di dalam slot otomatis bergaya. --}}
<div {{ $attributes->class(['flex w-full items-start gap-3 rounded-2xl border border-border/60 bg-surface-alt px-4 py-3 text-sm leading-relaxed text-text md:px-5 md:py-3.5 [&_a]:font-semibold [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4 [&_a]:hover:text-nav-hover']) }} role="note">
    <x-icon :name="$icon" class="mt-0.5 size-5 shrink-0 text-primary" />
    <p class="min-w-0 flex-1">{{ $slot }}</p>
</div>
