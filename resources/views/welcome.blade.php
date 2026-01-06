<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blustack Partner Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/blustack icon.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-slate-900 to-slate-950 blur-3xl opacity-60 pointer-events-none"></div>

    <div class="relative max-w-6xl mx-auto px-4 py-10 lg:py-14">
        <header class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/blustack icon.png') }}" alt="Blustack" class="w-10 h-10 object-contain">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-blue-300">TikTok Partner</p>
                    <h1 class="text-lg font-semibold">Blustack Operations Portal</h1>
                </div>
            </div>
            @if (Route::has('login'))
            <div class="flex items-center gap-3 text-sm">
                    @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors">Go to Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-700 hover:border-blue-500 hover:text-blue-200 transition-colors">Log in</a>
                    <a href="{{ route('contact') }}" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white transition-colors">Contact for team access</a>
                    @endauth
            </div>
            @endif
        </header>

        <main class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 text-blue-200 border border-blue-500/30 text-xs font-medium">
                    Trusted TikTok Shop Operations Partner
                </div>
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-white leading-tight">Operate TikTok Shops faster with one unified control center.</h2>
                    <p class="mt-3 text-slate-300 text-base leading-relaxed">Sync orders, standardize product templates, manage teams & permissions, and keep data safe with backups and audits — all in one place.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-sm font-semibold text-white mb-1">Order & fulfillment sync</div>
                        <p class="text-sm text-slate-400">Real-time orders, inventory, statuses, and tracking updates from TikTok Shop.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-sm font-semibold text-white mb-1">Product templates</div>
                        <p class="text-sm text-slate-400">Pre-validate attributes, media, and variants to cut publish errors.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-sm font-semibold text-white mb-1">Teams & roles</div>
                        <p class="text-sm text-slate-400">Granular permissions, activity logs, and safe role-based access.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                        <div class="text-sm font-semibold text-white mb-1">Backups & resilience</div>
                        <p class="text-sm text-slate-400">Automated backups, restore flows, and health checks for continuity.</p>
                    </div>
        </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="px-5 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-lg shadow-blue-500/20 transition-colors">Log in to portal</a>
                    <a href="mailto:team-access@hmtik.com" class="px-5 py-3 rounded-lg border border-slate-700 hover:border-blue-500 text-sm font-semibold text-slate-200 transition-colors">Contact staff for team access</a>
                </div>

                <div class="text-xs text-slate-400 space-x-3">
                    <span>24/7 Monitoring</span>
                    <span>•</span>
                    <span>PCI & GDPR aligned</span>
                    <span>•</span>
                    <span>Partner-grade SLAs</span>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-6 bg-gradient-to-br from-blue-600/20 via-indigo-500/10 to-transparent blur-3xl opacity-80"></div>
                <div class="relative rounded-2xl border border-slate-800 bg-slate-900/70 backdrop-blur-lg p-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.14em] text-blue-300">Live snapshot</p>
                            <h3 class="text-lg font-semibold text-white">Operations Overview</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs bg-green-500/15 text-green-300 border border-green-500/30">Healthy</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-800">
                            <p class="text-xs text-slate-400">Orders synced (24h)</p>
                            <p class="text-2xl font-bold text-white">1,284</p>
                            <p class="text-xs text-green-300 mt-1">+8% vs. prev day</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-800">
                            <p class="text-xs text-slate-400">Templates validated</p>
                            <p class="text-2xl font-bold text-white">312</p>
                            <p class="text-xs text-green-300 mt-1">0 publish errors</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-800">
                            <p class="text-xs text-slate-400">Backup status</p>
                            <p class="text-2xl font-bold text-white">OK</p>
                            <p class="text-xs text-slate-300 mt-1">Last run: 02:30 UTC</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-800">
                            <p class="text-xs text-slate-400">Users & roles</p>
                            <p class="text-2xl font-bold text-white">68</p>
                            <p class="text-xs text-slate-300 mt-1">RBAC enforced</p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-800/40 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                <p class="text-sm font-semibold text-white">TikTok Shop sync</p>
                            </div>
                            <span class="text-xs text-slate-300">Real-time webhooks</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-700 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-green-400 w-[78%]"></div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Latency p95: 680ms • Uptime: 99.95%</p>
                    </div>
                </div>
            </div>
        </main>

        <footer class="mt-10 text-xs text-slate-500 flex flex-wrap gap-3">
            <span>© {{ date('Y') }} Blustack. All rights reserved.</span>
            <span>•</span>
            <a href="#" class="hover:text-blue-300">Status</a>
            <a href="#" class="hover:text-blue-300">Docs</a>
            <a href="#" class="hover:text-blue-300">Support</a>
        </footer>
    </div>
    </body>
</html>

