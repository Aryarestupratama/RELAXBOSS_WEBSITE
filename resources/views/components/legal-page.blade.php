@props(['title', 'intro', 'image', 'imageAlt', 'toc'])
{{-- Kerangka halaman hukum Publik (Privasi, Ketentuan): hero, daftar isi menempel, isi, catatan penutup, kotak bantuan. --}}
<section class="overflow-hidden bg-neutral-soft">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-10 px-4 py-12 lg:py-14">
        <div class="max-w-2xl">
            <h1 data-load class="text-[1.75rem] leading-[1.2] font-semibold md:text-5xl md:leading-[1.15]">{{ $title }}</h1>
            <p data-load class="mt-5 max-w-prose text-lg text-text-secondary">{{ $intro }}</p>
        </div>
        <div data-load="pop" class="hidden w-64 shrink-0 md:block">
            <div class="overflow-hidden rounded-xl border border-border bg-neutral-soft shadow-card">
                <img src="{{ asset('images/legal/'.$image) }}" width="600" height="600" alt="{{ $imageAlt }}" class="h-auto w-full">
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
    <div class="grid items-start gap-12 lg:grid-cols-[14rem_minmax(0,1fr)]">
        <nav aria-label="Di halaman ini" class="hidden lg:sticky lg:top-24 lg:block">
            <p class="mb-3 text-sm font-medium text-text-secondary">Di halaman ini</p>
            <ul>
                @foreach ($toc as $id => $label)
                    <li>
                        <a href="#{{ $id }}" data-toc-link
                           class="block border-l-2 border-border py-1.5 pl-3 text-sm text-text-secondary transition-colors hover:text-text aria-[current=true]:border-brand aria-[current=true]:font-medium aria-[current=true]:text-text">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="max-w-3xl divide-y divide-border">
            {{ $slot }}
        </div>
    </div>
</section>

@isset($after)
    <section class="bg-neutral-soft" aria-label="Catatan penting">
        <div class="mx-auto max-w-3xl px-4 py-12">
            {{ $after }}
        </div>
    </section>
@endisset

<section class="mx-auto max-w-6xl px-4 py-12" aria-label="Bantuan">
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-crisis-bg p-5 text-crisis-text">
        <div>
            <h2 class="text-lg">Butuh bantuan sekarang?</h2>
            <p>Kamu tidak harus menghadapi ini sendirian.</p>
        </div>
        <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-2">Lihat halaman Konsultasi Profesional</a>
    </div>
</section>
