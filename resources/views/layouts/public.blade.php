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
    <link rel="icon" type="image/png" href="{{ asset('images/logo-relaxboss.png') }}">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/public.ts'])
</head>
<body class="flex min-h-screen flex-col bg-background text-text">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-4 focus:py-2 focus:shadow-popover">
        Lewati ke konten
    </a>

    <header class="border-b border-border bg-card md:sticky md:top-0 md:z-40 md:bg-card/90 md:backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ url('/') }}" class="inline-flex min-h-11 items-center gap-2 text-xl font-semibold text-text" aria-label="RelaxBoss, ke Beranda">
                <img src="{{ asset('images/logo-relaxboss.png') }}" width="32" height="32" alt="" class="size-8 rounded-lg">
                RelaxBoss
            </a>

            <nav aria-label="Navigasi utama" class="hidden items-center gap-8 md:flex">
                @foreach ($navLinks as $label => $link)
                    <a href="{{ url($link['href']) }}"
                       class="relative inline-flex min-h-11 items-center text-text-secondary transition-colors hover:text-text aria-[current=page]:font-medium aria-[current=page]:text-text after:absolute after:inset-x-0 after:bottom-1.5 after:h-0.5 after:origin-left after:scale-x-0 after:rounded-full after:bg-brand after:transition-transform after:duration-300 hover:after:scale-x-100 aria-[current=page]:after:scale-x-100"
                       @if (request()->is($link['pattern'])) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" data-press class="inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary px-6 py-2.5 font-medium text-primary transition duration-200 hover:bg-primary hover:text-on-primary">
                        Dashboard
                    </a>
                @else
                    <a href="{{ url('/masuk') }}" data-press class="inline-flex min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary px-5 py-2.5 font-medium text-primary transition duration-200 hover:bg-primary hover:text-on-primary">
                        Masuk
                    </a>
                    <a href="{{ url('/daftar') }}" data-press class="hidden min-h-11 items-center justify-center rounded-lg border-[1.5px] border-primary bg-primary px-5 py-2.5 font-medium text-on-primary transition duration-200 hover:shadow-card hover:brightness-130 sm:inline-flex">
                        Daftar gratis
                    </a>
                @endauth
            </div>
        </div>

        <nav aria-label="Navigasi utama di HP" class="border-t border-border md:hidden">
            <ul class="mx-auto flex max-w-6xl justify-around px-4">
                @foreach ($navLinks as $label => $link)
                    <li>
                        <a href="{{ url($link['href']) }}"
                           class="inline-flex min-h-11 items-center border-b-2 border-transparent px-3 text-sm text-text-secondary transition-colors hover:text-text aria-[current=page]:border-brand aria-[current=page]:font-medium aria-[current=page]:text-text"
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
                            <li><a href="{{ url($link['href']) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-text-secondary transition-colors hover:text-brand-strong">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 border-t border-border pt-5 text-sm text-text-muted">
                <p>&copy; {{ date('Y') }} RelaxBoss</p>
                <a href="{{ url('/privasi') }}" class="inline-flex min-h-11 items-center underline underline-offset-4 transition-colors hover:text-brand-strong">Privasi</a>
                <a href="{{ url('/ketentuan') }}" class="inline-flex min-h-11 items-center underline underline-offset-4 transition-colors hover:text-brand-strong">Ketentuan</a>
            </div>
        </div>
    </footer>
</body>
</html>
