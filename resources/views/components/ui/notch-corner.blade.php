@props(['href', 'label', 'hover' => 'group-hover:bg-nav-hover'])
{{-- Kontur dan cakram untuk kartu yang sudut kanan bawahnya dipotong takik (padanan Components/ui/notch-corner.tsx).
     Potongannya sendiri dibuat oleh kelas `notch-cut` pada kartu (masker SVG data URI di app.css, 74px); bentuknya
     lekuk dengan sudut dalam sepusat dengan cakram (cakram 44px, jari-jari lekuk 36px, jarak seragam 14px) dan dua
     ujung luar membulat 16px. Komponen ini hanya menggaris kontur potongan dengan --border/60 (sama seperti tepi
     kartu) dan memasang cakram panah biru gelap yang rapat ke pojok.
     Pasang di pembungkus `relative group` yang sisi luarnya sama dengan sisi luar kartu (bukan di dalam kartu ber-border),
     dan bungkus kartu saja (bukan cakram) dengan `drop-lift group-hover:drop-lift-hover`, kartunya `notch-cut shadow-none!`. Cakram adalah tautan 44px berlabel. --}}
<svg aria-hidden="true" viewBox="0 0 74 74" class="pointer-events-none absolute right-0 bottom-0 size-[74px]">
    <path d="M73.5 0A15.5 15.5 0 0 1 58 15.5L52 15.5A36.5 36.5 0 0 0 15.5 52L15.5 58A15.5 15.5 0 0 1 0 73.5" class="fill-none stroke-border/60" stroke-width="1" />
</svg>
<a href="{{ $href }}" aria-label="{{ $label }}"
   class="absolute right-0 bottom-0 grid size-11 place-items-center rounded-full bg-primary text-on-primary transition-[background-color,scale] duration-300 group-hover:scale-105 {{ $hover }}">
    <x-icon name="arrow-up-right" class="size-5 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
</a>
