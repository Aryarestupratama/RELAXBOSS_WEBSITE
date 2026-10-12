@props([
    'src',
    'alt',
    'width' => 900,
    'height' => 900,
])

@php
    // Posisi pil mengikuti letak karakter pada ilustrasi empat perasaan (persen dari lebar dan tinggi gambar):
    // Stres (merah) kiri atas, Cemas (kuning) kanan atas, Sedih (biru) kiri bawah, Lega (hijau) kanan bawah.
    // Pil berada tepat di bawah karakternya. Bila ilustrasi diganti, cukup sesuaikan `pos` di sini.
    $labels = [
        ['name' => 'Stres', 'dot' => 'bg-mood-red', 'pos' => 'left-[21%] top-[34%]'],
        ['name' => 'Cemas', 'dot' => 'bg-mood-yellow', 'pos' => 'left-[84%] top-[33%]'],
        ['name' => 'Sedih', 'dot' => 'bg-mood-blue', 'pos' => 'left-[18%] top-[81%]'],
        ['name' => 'Lega', 'dot' => 'bg-mood-green', 'pos' => 'left-[81%] top-[86%]'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'grid place-items-center overflow-hidden rounded-xl border border-border bg-card p-6 shadow-card']) }}>
    <div class="relative w-full max-w-sm">
        <img src="{{ $src }}" width="{{ $width }}" height="{{ $height }}" fetchpriority="high"
             alt="{{ $alt }}" class="h-auto w-full object-contain">

        {{-- Label emosi: hiasan, dipasang lewat kode agar teks tajam; disembunyikan di bawah 640px --}}
        <div class="hidden sm:block" aria-hidden="true">
            @foreach ($labels as $label)
                <span data-float class="absolute {{ $label['pos'] }}">
                    <span class="inline-flex -translate-x-1/2 items-center gap-2 rounded-full bg-card px-3 py-1 text-xs font-medium whitespace-nowrap text-text shadow-card">
                        <span class="size-3 rounded-full {{ $label['dot'] }}"></span>{{ $label['name'] }}
                    </span>
                </span>
            @endforeach
        </div>
    </div>
</div>
