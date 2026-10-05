@extends('layouts.public')

@section('title', 'Konsultasi Profesional | RelaxBoss')
@section('description', 'Kapan sebaiknya mencari bantuan profesional untuk stres, dan daftar kontak bantuan yang bisa kamu hubungi.')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
        <h1>Konsultasi profesional</h1>
        <p class="mt-4 text-text-secondary">
            RelaxBoss membantu mengenali kondisimu, tetapi tidak menggantikan psikolog atau tenaga kesehatan jiwa.
            Bicara dengan profesional adalah langkah yang wajar dan tidak perlu menunggu sampai keadaan parah.
        </p>

        {{-- Kotak darurat --}}
        <section class="mt-8 rounded-xl border border-crisis-text/30 bg-crisis-bg p-5 text-crisis-text" aria-labelledby="darurat">
            <h2 id="darurat">Kalau kamu sedang dalam bahaya</h2>
            <p class="mt-2">
                Kalau kamu berpikir untuk menyakiti dirimu sendiri, tolong hubungi bantuan sekarang atau minta seseorang yang kamu percaya menemanimu.
                Kamu tidak harus menghadapi ini sendirian.
            </p>
        </section>

        <section class="mt-8" aria-labelledby="kapan">
            <h2 id="kapan">Kapan sebaiknya mencari bantuan</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-text-secondary">
                <li>Rasa tertekan berlangsung berminggu-minggu dan mengganggu kuliah, tidur, atau makanmu.</li>
                <li>Kamu sulit menikmati hal yang dulu kamu sukai.</li>
                <li>Kamu merasa kewalahan terus-menerus dan sulit mengatasinya sendiri.</li>
                <li>Orang terdekat mulai mengkhawatirkan keadaanmu.</li>
                <li>Kamu punya pikiran untuk menyakiti diri sendiri.</li>
            </ul>
        </section>

        <section class="mt-8" aria-labelledby="kontak">
            <h2 id="kontak">Kontak bantuan</h2>

            @if (count($contacts) === 0)
                <p class="mt-3 text-text-secondary">Daftar kontak sedang disiapkan.</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($contacts as $contact)
                        @php
                            $phone = $contact['phone'] ?? null;
                            $link = $contact['url'] ?? null;
                            $isDummy = (bool) ($contact['dummy'] ?? false);
                        @endphp
                        <li class="rounded-xl border border-border bg-card p-4 shadow-card">
                            <p class="flex items-center gap-2 font-medium text-text">
                                <x-icon name="phone" class="size-5 text-brand-strong" />
                                {{ $contact['name'] ?? '' }}
                            </p>
                            @if (filled($phone))
                                <p class="mt-1">
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2">{{ $phone }}</a>
                                </p>
                            @endif
                            @if (is_string($link) && str_starts_with($link, 'https://'))
                                <p class="mt-1">
                                    <a href="{{ $link }}" rel="noopener noreferrer" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-2">{{ $link }}</a>
                                </p>
                            @endif
                            @if (filled($contact['note'] ?? null) && ! $isDummy)
                                <p class="mt-1 text-sm text-text-secondary">{{ $contact['note'] }}</p>
                            @endif
                            @if ($isDummy)
                                <p class="mt-1 text-sm text-warning">Contoh sementara, bukan kontak yang sebenarnya.</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="mt-10">
            <x-disclaimer>
                RelaxBoss bukan psikolog atau layanan medis, dan hasil Asesmen bukan diagnosis.
            </x-disclaimer>
        </div>
    </article>
@endsection
