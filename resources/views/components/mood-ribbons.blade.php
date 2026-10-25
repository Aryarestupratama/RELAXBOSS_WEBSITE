{{--
    Pita mood: lima pita berwajah dalam warna skala mood (1 ungu, 2 biru, 3 persik, 4 hijau, 5 kuning).
    Hiasan murni; warna dari token lewat var(--...), garis wajah memakai --text.
    Pakai: <x-mood-ribbons class="w-full" />. Berikan label bila gambar membawa makna.
--}}
@props(['label' => null])
@php $uid = 'mr'.substr(md5(uniqid('', true)), 0, 6); @endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 440" fill="none" overflow="visible"
     @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif
     {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $uid }}-fade" gradientUnits="userSpaceOnUse" x1="0" y1="250" x2="0" y2="440">
            <stop offset="0" stop-color="#fff"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
        <mask id="{{ $uid }}-mask" maskUnits="userSpaceOnUse" x="-300" y="-100" width="1200" height="640">
            <rect x="-300" y="-100" width="1200" height="640" fill="url(#{{ $uid }}-fade)"/>
            <rect x="-300" y="-100" width="1200" height="350" fill="#fff"/>
        </mask>
    </defs>

    <g mask="url(#{{ $uid }}-mask)" stroke-width="34" stroke-linecap="round" stroke-linejoin="round">
        <path d="M110 170C115 260 200 300 290 330S450 385 1100 365" stroke="var(--mood-purple)"/>
        <path d="M205 110C210 200 130 250 110 330S120 440 150 480" stroke="var(--mood-blue)"/>
        <path d="M300 205C300 275 360 305 430 335S520 405 1100 425" stroke="var(--mood-peach)"/>
        <path d="M390 95C395 190 320 240 300 320S320 440 340 480" stroke="var(--mood-green)"/>
        <path d="M485 150C490 240 545 280 525 350S485 440 475 480" stroke="var(--mood-yellow)"/>
    </g>

    {{-- Wajah di ujung tiap pita --}}
    <g stroke="var(--text)" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
        <g transform="translate(110 168)">
            <circle cx="-7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/><circle cx="7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/>
            <path d="M-6 9Q0 3 6 9"/>
        </g>
        <g transform="translate(205 108)">
            <circle cx="-7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/><circle cx="7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/>
            <path d="M-6 7Q-3 4 0 7T6 7"/>
        </g>
        <g transform="translate(300 203)">
            <circle cx="-7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/><circle cx="7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/>
            <path d="M-5 7H5"/>
        </g>
        <g transform="translate(390 93)">
            <circle cx="-7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/><circle cx="7" cy="-3" r="2.4" fill="var(--text)" stroke="none"/>
            <path d="M-6 4Q0 11 6 4"/>
        </g>
        <g transform="translate(485 148)">
            <path d="M-10 -3Q-7 -7 -4 -3M4 -3Q7 -7 10 -3"/>
            <path d="M-6 3Q0 13 6 3Z" fill="var(--text)"/>
        </g>
    </g>
</svg>
