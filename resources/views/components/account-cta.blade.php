@props(['label'])
{{-- Tombol ajakan utama halaman Publik: tamu diajak mendaftar, pengguna kembali ke Dashboard, admin ke Panel Admin. --}}
@auth
    @if (auth()->user()->isAdmin())
        <x-button-link href="{{ url('/admin') }}" {{ $attributes }}>Buka Panel Admin</x-button-link>
    @else
        <x-button-link href="{{ url('/dashboard') }}" {{ $attributes }}>Ke Dashboard</x-button-link>
    @endif
@else
    <x-button-link href="{{ url('/daftar') }}" {{ $attributes }}>{{ $label }}</x-button-link>
@endauth
