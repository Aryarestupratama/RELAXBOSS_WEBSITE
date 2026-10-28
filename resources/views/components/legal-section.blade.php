@props(['id', 'title', 'icon', 'number' => null, 'tone' => 'yellow'])
{{--
    Satu bagian dokumen hukum. Berada di dalam lembar putih bersama bagian lain (dipisah garis tipis), bukan kartu sendiri.
    Label "Bagian 01" mengikuti nomor di daftar isi; isi sejajar dengan judul. `tone` dipertahankan agar pemanggil lama tidak berubah.
--}}
<section id="{{ $id }}" aria-labelledby="{{ $id }}-judul" data-reveal class="scroll-mt-24 px-6 py-8 md:px-10 md:py-10">
    @if ($number !== null)
        <p class="text-xs font-semibold tracking-[0.14em] text-primary uppercase">Bagian {{ sprintf('%02d', $number) }}</p>
    @endif
    <div class="{{ $number !== null ? 'mt-3' : '' }} flex items-center gap-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-soft-lavender text-text" aria-hidden="true"><x-icon name="{{ $icon }}" class="size-[1.125rem]" /></span>
        <h2 id="{{ $id }}-judul" class="text-xl leading-snug font-bold tracking-tight md:text-2xl">{{ $title }}</h2>
    </div>
    <div class="mt-4 space-y-3 leading-relaxed text-text-secondary md:pl-[3.25rem] [&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:decoration-primary/40 [&_a]:underline-offset-4 [&_a]:transition-colors [&_a:hover]:text-nav-hover [&_a:hover]:decoration-nav-hover">
        {{ $slot }}
    </div>
</section>
