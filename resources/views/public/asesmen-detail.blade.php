@extends('layouts.public')

@section('title', \Illuminate\Support\Str::limit($assessment['name'], 41, '…').' | Asesmen RelaxBoss')
@section('description', \Illuminate\Support\Str::limit(strip_tags($assessment['description']), 155))

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12 md:py-16">
        <p>
            <a href="{{ url('/asesmen') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Semua asesmen</a>
        </p>

        <h1 class="mt-2">{{ $assessment['name'] }}</h1>

        @if ($assessment['is_demo'])
            <p class="mt-2 text-sm text-warning">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
        @endif

        <p class="mt-4 text-text-secondary">{{ $assessment['description'] }}</p>

        <p class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-1 text-text-secondary">
            <span class="inline-flex items-center gap-2">
                <x-icon name="clock" class="size-5 text-brand-strong" />
                Sekitar {{ $assessment['estimated_minutes'] }} menit
            </span>
            <span class="inline-flex items-center gap-2">
                <x-icon name="list-checks" class="size-5 text-brand-strong" />
                {{ $assessment['question_count'] }} pertanyaan
            </span>
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <x-button-link href="{{ url('/app/asesmen/'.$assessment['slug']) }}">Mulai asesmen</x-button-link>
            @guest
                <x-button-link href="{{ url('/daftar') }}" variant="outline">Daftar gratis</x-button-link>
            @endguest
        </div>
        @guest
            <p class="mt-3 text-sm text-text-secondary">
                Kamu perlu masuk untuk mengerjakan asesmen. Hasilnya tersimpan di akunmu dan bisa dilihat lagi di riwayat.
            </p>
        @endguest

        @if (count($assessment['sub_scales']) > 0)
            <section class="mt-10" aria-labelledby="aspek">
                <h2 id="aspek">Yang dilihat</h2>
                <p class="mt-2 text-text-secondary">Hasilmu ditampilkan terpisah untuk tiap aspek berikut.</p>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($assessment['sub_scales'] as $subScale)
                        <li class="rounded-full bg-neutral-soft px-3 py-1 text-text">{{ $subScale }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-10" aria-labelledby="cara">
            <h2 id="cara">Cara mengerjakan</h2>
            @if (filled($assessment['instructions']))
                <p class="mt-2 text-text-secondary">{{ $assessment['instructions'] }}</p>
            @endif
            @if (count($assessment['options']) > 0)
                <p class="mt-3 text-text-secondary">Pilihan jawaban: {{ implode(', ', $assessment['options']) }}.</p>
            @endif
            <p class="mt-3 text-text-secondary">Satu pertanyaan tampil pada satu waktu. Tidak ada jawaban benar atau salah, jawab sesuai keadaanmu.</p>
        </section>

        @if (filled($assessment['source']) || filled($assessment['validated_by']))
            <section class="mt-10" aria-labelledby="sumber">
                <h2 id="sumber">Tentang instrumen</h2>
                <dl class="mt-3 space-y-2 text-text-secondary">
                    @if (filled($assessment['source']))
                        <div>
                            <dt class="text-sm">Sumber</dt>
                            <dd class="text-text">{{ $assessment['source'] }}</dd>
                        </div>
                    @endif
                    @if (filled($assessment['validated_by']))
                        <div>
                            <dt class="text-sm">Divalidasi oleh</dt>
                            <dd class="text-text">{{ $assessment['validated_by'] }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        @endif

        <div class="mt-10">
            <x-disclaimer>
                Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
            </x-disclaimer>
        </div>

        <p class="mt-6">
            <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Bicara dengan profesional</a>
        </p>
    </article>
@endsection
