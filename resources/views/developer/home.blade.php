@extends('layouts.developer')
@section('title', 'Apps')
@section('content')

<div class="mb-8 text-center">
    <h1 class="text-2xl md:text-3xl font-bold text-white">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
    <p class="text-slate-400 text-sm mt-2">Choose an application to open.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-5xl mx-auto">

    {{-- CTS — the live ticketing system --}}
    <a href="{{ route('dashboard') }}"
       class="dev-card group block bg-gradient-to-br from-sky-800 to-slate-900 border border-sky-700 rounded-2xl p-6 hover:border-sky-400">
        <div class="h-14 w-14 rounded-xl bg-sky-500/20 text-sky-300 flex items-center justify-center mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-white">CTS</div>
        <div class="text-xs text-sky-300/80 mb-2">Corporate Ticketing System</div>
        <p class="text-sm text-slate-300 leading-relaxed">
            The live ticketing app — tickets, lifecycle, TAT/SLA, dashboards, expenses, reports. This is the production system.
        </p>
        <div class="mt-4 text-xs text-sky-300 font-medium group-hover:translate-x-0.5 transition">Open CTS →</div>
    </a>

    {{-- ATS — asset management (still incubating) --}}
    <a href="{{ route('developer.assets') }}"
       class="dev-card group block bg-gradient-to-br from-indigo-800 to-slate-900 border border-indigo-700 rounded-2xl p-6 hover:border-indigo-400">
        <div class="h-14 w-14 rounded-xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14L12 11m0 10V11m0 0L4 7m8 14l-8-4V7"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-white">ATS</div>
        <div class="text-xs text-indigo-300/80 mb-2">Asset Management System</div>
        <p class="text-sm text-slate-300 leading-relaxed">
            Physical + software asset register across every branch — assignment, lifecycle events, roster reconciliation. Scaffold, awaiting scope-of-work.
        </p>
        <div class="mt-4 text-xs text-indigo-300 font-medium group-hover:translate-x-0.5 transition">Open ATS →</div>
    </a>

    {{-- Dialer — Smartping cloud telephony --}}
    <a href="{{ route('developer.dialer.home') }}"
       class="dev-card group block bg-gradient-to-br from-emerald-800 to-slate-900 border border-emerald-700 rounded-2xl p-6 hover:border-emerald-400">
        <div class="h-14 w-14 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-white">Dialer</div>
        <div class="text-xs text-emerald-300/80 mb-2">Smartping cloud telephony · 1600 toll-free</div>
        <p class="text-sm text-slate-300 leading-relaxed">
            Inbound + outbound calling on Smartping. Customer database, CSV import, auto-created dialer tickets, call trail, missed calls and recording playback.
        </p>
        <div class="mt-4 text-xs text-emerald-300 font-medium group-hover:translate-x-0.5 transition">Open Dialer →</div>
    </a>

</div>

<div class="mt-10 max-w-5xl mx-auto bg-slate-800/40 border border-slate-700 rounded-lg p-5">
    <div class="flex items-center gap-2 text-sm text-slate-300 font-semibold mb-2">
        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a1 1 0 011 1v3a1 1 0 11-2 0V7a1 1 0 011-1zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        Heads up
    </div>
    <ul class="text-xs text-slate-400 space-y-1 list-disc list-inside">
        <li>ATS and Dialer are only visible under the developer role — admin / CISO / management / employees don't see them yet.</li>
        <li>CTS is the real system. Anything you do there is live data.</li>
        <li>Promote ATS / Dialer to the main app (sidebar, other roles) only after sign-off.</li>
    </ul>
</div>
@endsection
