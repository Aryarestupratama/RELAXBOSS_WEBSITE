@php
    $defaultDescription = 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.';
    $navLinks = [
        'Asesmen' => ['pattern' => 'asesmen*', 'href' => '/asesmen'],
        'Tentang' => ['pattern' => 'tentang', 'href' => '/tentang'],
        'Konsultasi' => ['pattern' => 'konsultasi', 'href' => '/konsultasi'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RelaxBoss')</title>
    <meta name="description" content="@yield('description', $defaultDescription)">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="RelaxBoss">
    <meta property="og:title" content="@yield('title', 'RelaxBoss')">
    <meta property="og:description" content="@yield('description', $defaultDescription)">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-relaxboss.png') }}">
    @stack('head')
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-background text-text">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover">
        Lewati ke konten
    </a>

    <header class="border-b border-border bg-card">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 text-xl font-semibold text-text" aria-label="RelaxBoss, ke Beranda">
                <img src="{{ asset('images/logo-relaxboss.png') }}" width="32" height="32" alt="" class="size-8 rounded-lg">
                RelaxBoss
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-6 md:flex">
                @foreach ($navLinks as $label => $link)
                    <a href="{{ url($link['href']) }}"
                       class="text-text-secondary transition-colors hover:text-brand-strong"
                       @if (request()->is($link['pattern'])) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary px-6 py-2.5 font-medium text-primary transition-colors hover:bg-primary hover:text-on-primary">
                    Dashboard
                </a>
            @else
                <a href="{{ url('/masuk') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary px-6 py-2.5 font-medium text-primary transition-colors hover:bg-primary hover:text-on-primary">
                    Masuk
                </a>
            @endauth
        </div>

        <nav aria-label="Navigasi utama di HP" class="border-t border-border md:hidden">
            <ul class="mx-auto flex max-w-6xl justify-around px-4">
                @foreach ($navLinks as $label => $link)
                    <li>
                        <a href="{{ url($link['href']) }}"
                           class="inline-flex min-h-11 items-center px-3 text-sm text-text-secondary transition-colors hover:text-brand-strong"
                           @if (request()->is($link['pattern'])) aria-current="page" @endif>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-border">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm text-text-secondary sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} RelaxBoss</p>
            <nav aria-label="Tautan footer">
                <ul class="flex flex-wrap gap-x-6 gap-y-1">
                    <li><a href="{{ url('/konsultasi') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong">Butuh bantuan sekarang?</a></li>
                    <li><a href="{{ url('/privasi') }}" class="inline-flex min-h-11 items-center transition-colors hover:text-brand-strong">Privasi</a></li>
                    <li><a href="{{ url('/ketentuan') }}" class="inline-flex min-h-11 items-center transition-colors hover:text-brand-strong">Ketentuan</a></li>
                </ul>
            </nav>
        </div>
    </footer>
</body>
</html>
