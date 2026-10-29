@php
    $defaultDescription = 'Kenali kondisimu lewat asesmen, pantau mood harian, dan bercerita kapan saja dengan RelaxMate. Gratis, langsung dari browser.';
    $navLinks = [
        'Asesmen' => ['pattern' => 'asesmen*', 'href' => '/asesmen'],
        'Artikel' => ['pattern' => 'artikel*', 'href' => '/artikel'],
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
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="RelaxBoss">
    <meta property="og:title" content="@yield('title', 'RelaxBoss')">
    <meta property="og:description" content="@yield('description', $defaultDescription)">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    @include('layouts.partials.head-icons')
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/public.ts'])
</head>
<body class="flex min-h-screen flex-col bg-background text-text">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover">
        Lewati ke konten
    </a>

    @php
        // Gerak hover satu kurva dengan halaman Publik lain (motion.ts: [0.2, 0.7, 0.2, 1]).
        $ease = 'ease-[cubic-bezier(0.2,0.7,0.2,1)]';
        // Tautan: latar pil lembut membesar dari tengah dan teks beralih ke --nav-hover (tanpa garis bawah).
        // ::before = pil, ::after = area sentuh 44px agar tampilan kecil tetap mudah ditekan.
        $navLink = 'group relative isolate inline-flex h-9 items-center rounded-full px-2 text-sm font-medium text-text-secondary transition-colors min-[375px]:px-3.5 duration-300 '.$ease
            .' before:absolute before:inset-0 before:-z-10 before:scale-75 before:rounded-full before:bg-nav-hover/[0.07] before:opacity-0 before:transition-all before:duration-300 before:ease-[cubic-bezier(0.2,0.7,0.2,1)]'
            .' after:absolute after:inset-x-0 after:-inset-y-1 hover:text-nav-hover hover:before:scale-100 hover:before:opacity-100'
            .' aria-[current=page]:text-primary aria-[current=page]:before:scale-100 aria-[current=page]:before:bg-brand-soft/30 aria-[current=page]:before:opacity-100';
        $btnBase = 'inline-flex h-10 items-center justify-center whitespace-nowrap rounded-lg border-[1.5px] px-3 text-sm font-medium transition-all sm:px-4 duration-300 '.$ease;
        $btnOutline = $btnBase.' border-primary text-primary hover:border-nav-hover hover:bg-nav-hover hover:text-on-primary hover:shadow-lift';
        $btnSolid = $btnBase.' border-primary bg-primary text-on-primary hover:-translate-y-px hover:border-nav-hover hover:bg-nav-hover hover:shadow-lift-hover';
    @endphp

    <header class="border-b border-border bg-card md:sticky md:top-0 md:z-40 md:bg-card/90 md:backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center gap-2 px-4 py-2 sm:gap-4">
            <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 text-base font-semibold text-text" aria-label="RelaxBoss, ke Beranda">
                <img src="{{ asset('images/logo-relaxboss.png') }}" width="28" height="28" alt="" class="size-7 rounded-lg">
                RelaxBoss
            </a>

            <span class="hidden h-6 w-px bg-border md:block" aria-hidden="true"></span>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-1 md:flex">
                @foreach ($navLinks as $label => $link)
                    <a href="{{ url($link['href']) }}" class="{{ $navLink }}"
                       @if (request()->is($link['pattern'])) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/dashboard') }}" data-press class="max-sm:hidden {{ $btnOutline }}">Dashboard</a>
                        <a href="{{ url('/admin') }}" data-press class="{{ $btnSolid }}">Panel Admin</a>
                    @else
                        <a href="{{ url('/dashboard') }}" data-press class="{{ $btnSolid }}">Dashboard</a>
                    @endif
                @else
                    <a href="{{ url('/masuk') }}" data-press class="{{ $btnOutline }}">Masuk</a>
                    <a href="{{ url('/daftar') }}" data-press class="{{ $btnSolid }}"><span class="sm:hidden">Daftar</span><span class="max-sm:hidden">Daftar gratis</span></a>
                @endauth
            </div>
        </div>

        <nav aria-label="Navigasi utama di HP" class="border-t border-border md:hidden">
            <ul class="mx-auto flex max-w-6xl justify-between overflow-x-auto px-2 py-1.5 min-[375px]:px-4">
                @foreach ($navLinks as $label => $link)
                    <li>
                        <a href="{{ url($link['href']) }}" class="{{ $navLink }}"
                           @if (request()->is($link['pattern'])) aria-current="page" @endif>{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-surface-alt">
        <div class="mx-auto max-w-6xl px-4 pt-9 pb-6">
            <div class="flex flex-col items-center justify-between gap-5 md:flex-row">
                <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 text-xl font-semibold text-text" aria-label="RelaxBoss, ke Beranda">
                    <img src="{{ asset('images/logo-relaxboss.png') }}" width="32" height="32" alt="" class="size-8 rounded-lg">
                    RelaxBoss
                </a>

                <nav aria-label="Jelajahi">
                    <ul class="flex flex-wrap justify-center gap-x-7">
                        @foreach ($navLinks as $label => $link)
                            <li><a href="{{ url($link['href']) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-text-secondary transition-colors hover:text-primary">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 border-t border-border pt-5 text-sm text-text-muted">
                <p>&copy; {{ date('Y') }} RelaxBoss</p>
                <a href="{{ url('/privasi') }}" class="inline-flex min-h-11 items-center underline underline-offset-4 transition-colors hover:text-primary">Privasi</a>
                <a href="{{ url('/ketentuan') }}" class="inline-flex min-h-11 items-center underline underline-offset-4 transition-colors hover:text-primary">Ketentuan</a>
            </div>
        </div>
    </footer>
</body>
</html>
