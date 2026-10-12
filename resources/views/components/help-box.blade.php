<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-3 rounded-xl bg-mood-red/25 p-5 text-text']) }}>
    <div>
        <h2 class="text-lg">Butuh bantuan sekarang?</h2>
        <p>Kamu tidak harus menghadapi ini sendirian.</p>
    </div>
    <a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-2">Lihat halaman Konsultasi Profesional</a>
</div>
