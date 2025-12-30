<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact | HMTik Partner</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-slate-900 to-slate-950 blur-3xl opacity-60 pointer-events-none"></div>

    <div class="relative max-w-4xl mx-auto px-4 py-10 lg:py-14">
        <header class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                    H
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-blue-300">TikTok Partner</p>
                    <h1 class="text-lg font-semibold">Contact HMTik</h1>
                </div>
            </div>
            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-700 hover:border-blue-500 hover:text-blue-200 transition-colors">Log in</a>
                @endauth
            </div>
        </header>

        <main class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-4">
                <p class="text-sm text-blue-200 uppercase tracking-[0.14em]">Need team access?</p>
                <h2 class="text-3xl font-bold text-white leading-tight">Contact our staff to provision your team account.</h2>
                <p class="text-slate-300 leading-relaxed">We’ll help you set up team access, roles, and TikTok Shop integrations. Reach out via the channels below.</p>
                <div class="flex flex-col gap-3">
                    <a href="mailto:team-access@hmtik.com" class="inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-colors">
                        <span>team-access@hmtik.com</span>
                    </a>
                    <a href="mailto:support@hmtik.com" class="inline-flex items-center gap-2 px-4 py-3 rounded-lg border border-slate-700 hover:border-blue-500 text-sm font-semibold text-slate-200 transition-colors">
                        <span>support@hmtik.com</span>
                    </a>
                </div>
                <div class="text-xs text-slate-400">Response window: Mon–Sat, 08:00–20:00 (GMT+7). For urgent incidents, include “[URGENT]” in the subject.</div>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur-lg p-6 shadow-2xl space-y-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-blue-300">Social & support</p>
                    <h3 class="text-lg font-semibold text-white">Stay connected</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">LinkedIn</span>
                        <span class="text-xs text-slate-400">Updates & news</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">Facebook</span>
                        <span class="text-xs text-slate-400">Community</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">TikTok</span>
                        <span class="text-xs text-slate-400">Product tips</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">YouTube</span>
                        <span class="text-xs text-slate-400">How-to videos</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">Support Center</span>
                        <span class="text-xs text-slate-400">Knowledge base</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-slate-800 hover:border-blue-500 transition-colors">
                        <span class="text-white font-semibold">Status Page</span>
                        <span class="text-xs text-slate-400">Uptime & incidents</span>
                    </a>
                </div>
            </div>
        </main>

        <footer class="mt-10 text-xs text-slate-500 flex flex-wrap gap-3">
            <span>© {{ date('Y') }} HMTik. All rights reserved.</span>
            <span>•</span>
            <a href="#" class="hover:text-blue-300">Status</a>
            <a href="#" class="hover:text-blue-300">Docs</a>
            <a href="#" class="hover:text-blue-300">Support</a>
        </footer>
    </div>
</body>
</html>

