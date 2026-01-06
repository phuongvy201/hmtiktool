@extends('layouts.public')

@section('title', 'Contact | BluStack')

@section('header-subtitle', 'BluStack')
@section('header-title', 'Contact BluStack')

@section('header-logo')
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
        <i class="fas fa-envelope"></i>
    </div>
@endsection

@section('auth-button-text', 'Dashboard')

@section('container-class', 'max-w-4xl')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="space-y-4">
        <p class="text-sm text-blue-200 uppercase tracking-[0.14em]">Need team access?</p>
        <h2 class="text-3xl font-bold text-white leading-tight">Contact our staff to provision your team account.</h2>
        <p class="text-slate-300 leading-relaxed">We'll help you set up team access, roles, and TikTok Shop integrations. Reach out via the channels below.</p>
        <div class="flex flex-col gap-3">
            <a href="mailto:team-access@blustack.com" class="inline-flex items-center gap-2 px-4 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition-colors">
                <span>team-access@blustack.com</span>
            </a>
            <a href="mailto:support@blustack.com" class="inline-flex items-center gap-2 px-4 py-3 rounded-lg border border-slate-700 hover:border-blue-500 text-sm font-semibold text-slate-200 transition-colors">
                <span>support@blustack.com</span>
            </a>
        </div>
        <div class="text-xs text-slate-400">Response window: Mon–Sat, 08:00–20:00 (GMT+7). For urgent incidents, include "[URGENT]" in the subject.</div>
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
</div>
@endsection

@section('footer-links')
    <span>•</span>
    <a href="#" class="hover:text-blue-300">Status</a>
    <a href="#" class="hover:text-blue-300">Docs</a>
    <a href="#" class="hover:text-blue-300">Support</a>
@endsection
