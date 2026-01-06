<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BluStack')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/blustack icon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-slate-900 to-slate-950 blur-3xl opacity-60 pointer-events-none"></div>

    <div class="relative @yield('container-class', 'max-w-6xl') mx-auto px-4 py-10 lg:py-14">
        <header class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-3">
                @hasSection('header-logo')
                    @yield('header-logo')
                @else
                    <img src="{{ asset('images/blustack icon.png') }}" alt="Blustack" class="w-10 h-10 object-contain">
                @endif
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-blue-300">@yield('header-subtitle', 'TikTok Partner')</p>
                    <h1 class="text-lg font-semibold">@yield('header-title', 'Blustack Operations Portal')</h1>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors">@yield('auth-button-text', 'Go to Dashboard')</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-700 hover:border-blue-500 hover:text-blue-200 transition-colors">Log in</a>
                    @hasSection('header-extra-button')
                        @yield('header-extra-button')
                    @endif
                @endauth
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mt-10 text-xs text-slate-500 flex flex-wrap gap-3">
            <span>© {{ date('Y') }} Blustack. All rights reserved.</span>
            <span>•</span>
            <a href="{{ route('security-policy') }}" class="hover:text-blue-300">Security Policy</a>
            <span>•</span>
            <a href="{{ route('contact') }}" class="hover:text-blue-300">Contact</a>
            @hasSection('footer-links')
                @yield('footer-links')
            @else
                <span>•</span>
                <a href="#" class="hover:text-blue-300">Status</a>
                <a href="#" class="hover:text-blue-300">Docs</a>
                <a href="#" class="hover:text-blue-300">Support</a>
            @endif
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
