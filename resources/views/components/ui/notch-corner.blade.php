@props(['href', 'label', 'hover' => 'group-hover:bg-nav-hover'])
{{-- Takik sudut kanan bawah kartu + cakram panah (padanan Components/ui/notch-corner.tsx).
     Warna takik = --background (permukaan di belakang kartu). Pasang di dalam elemen `relative group`.
     Cakram adalah tautan 44px berlabel; lapisan takik dekoratif. --}}
<span aria-hidden="true" class="pointer-events-none absolute right-0 bottom-0 size-14 rounded-tl-[34px] rounded-br-3xl bg-background"></span>
<span aria-hidden="true" class="pointer-events-none absolute right-0 bottom-14 size-5 bg-[radial-gradient(circle_at_top_left,transparent_19.5px,var(--background)_20px)]"></span>
<span aria-hidden="true" class="pointer-events-none absolute right-14 bottom-0 size-5 bg-[radial-gradient(circle_at_top_left,transparent_19.5px,var(--background)_20px)]"></span>
<a href="{{ $href }}" aria-label="{{ $label }}"
   class="absolute right-0 bottom-0 grid size-11 place-items-center rounded-full bg-soft-lavender text-primary transition-[background-color,color,scale] duration-300 group-hover:scale-105 {{ $hover }} group-hover:text-on-primary">
    <x-icon name="arrow-up-right" class="size-5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
</a>
