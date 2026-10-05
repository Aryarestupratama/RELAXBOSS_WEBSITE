@extends('layouts.public')

@section('title', $heading.' | RelaxBoss')
@section('description', $message)

@push('head')
    <meta name="robots" content="noindex">
@endpush

@section('content')
    <div class="mx-auto flex max-w-xl flex-col items-center px-4 py-16 text-center sm:py-24">
        <p class="text-sm text-text-muted">Kode {{ $code }}</p>
        <h1 class="mt-2">{{ $heading }}</h1>
        <p class="mt-4 text-text-secondary">{{ $message }}</p>
        <a href="{{ url('/') }}" class="mt-8 inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary bg-primary px-6 py-2.5 font-medium text-on-primary transition hover:brightness-130">
            Kembali ke beranda
        </a>
    </div>
@endsection
