@extends('layouts.public')

@section('title', 'Daftar Asesmen | RelaxBoss')
@section('description', 'Pilih asesmen untuk mengenali kondisimu: stres, kecemasan, dan kelelahan. Gratis, hasilnya bisa kamu lihat lagi kapan saja.')

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
        <h1>Asesmen</h1>
        <p class="mt-4 max-w-prose text-text-secondary">
            Jawab beberapa pertanyaan dan lihat gambaran awal kondisimu. Pilih asesmen yang ingin kamu coba,
            lalu masuk atau daftar gratis untuk mengerjakannya.
        </p>

        @if ($assessments->isEmpty())
            <div class="mt-8 rounded-xl border border-border bg-card p-6 text-center shadow-card">
                <x-icon name="clipboard-check" class="mx-auto size-8 text-brand-strong" />
                <p class="mt-3 text-text-secondary">Daftar asesmen sedang disiapkan.</p>
            </div>
        @else
            <ul class="mt-8 grid gap-4 md:grid-cols-2">
                @foreach ($assessments as $item)
                    <li class="flex flex-col rounded-xl border border-border bg-card p-5 shadow-card">
                        <h2 class="text-xl">
                            <a href="{{ url('/asesmen/'.$item['slug']) }}" class="text-text hover:text-brand-strong">{{ $item['name'] }}</a>
                        </h2>

                        @if ($item['is_demo'])
                            <p class="mt-1 text-sm text-warning">Instrumen contoh untuk pengembangan, bukan asesmen yang sebenarnya.</p>
                        @endif

                        <p class="mt-2 text-text-secondary">{{ $item['summary'] }}</p>

                        <p class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-text-secondary">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="clock" class="size-4 text-brand-strong" />
                                Sekitar {{ $item['estimated_minutes'] }} menit
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="list-checks" class="size-4 text-brand-strong" />
                                {{ $item['question_count'] }} pertanyaan
                            </span>
                        </p>

                        @if (count($item['sub_scales']) > 0)
                            <ul class="mt-3 flex flex-wrap gap-2" aria-label="Aspek yang diukur">
                                @foreach ($item['sub_scales'] as $subScale)
                                    <li class="rounded-full bg-neutral-soft px-3 py-1 text-sm text-text">{{ $subScale }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-5 pt-1">
                            <x-button-link href="{{ url('/asesmen/'.$item['slug']) }}" variant="outline" aria-label="Lihat detail {{ $item['name'] }}">Lihat detail</x-button-link>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-10">
            <x-disclaimer>
                Hasil ini bukan diagnosis. Ini gambaran awal untuk membantumu mengenali kondisimu. Untuk penilaian yang tepat, bicarakan dengan psikolog atau tenaga profesional.
            </x-disclaimer>
        </div>
    </section>
@endsection
