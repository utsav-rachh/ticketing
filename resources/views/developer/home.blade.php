@extends('layouts.developer')
@section('title', 'Apps')
@section('content')

<div class="mb-6 md:mb-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
    <p class="text-gray-500 text-sm mt-1">Choose an application to open.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-5">

    {{-- CTS — the live ticketing system --}}
    <a href="{{ route('dashboard') }}"
       class="dev-card group block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:border-brand-400">
        <div class="h-12 w-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-4 overflow-hidden">
            <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-gray-800">CTS</div>
        <div class="text-xs text-brand-600 mb-2">Corporate Ticketing System</div>
        <p class="text-sm text-gray-600 leading-relaxed">
            The live ticketing app — tickets, lifecycle, TAT/SLA, dashboards, expenses, reports. This is the production system.
        </p>
        <div class="mt-4 text-xs text-brand-600 font-medium group-hover:translate-x-0.5 transition">Open CTS →</div>
    </a>

    {{-- ATS — asset management (still incubating) --}}
    <a href="{{ route('developer.assets') }}"
       class="dev-card group block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:border-gold-400">
        <div class="h-12 w-12 rounded-lg bg-gold-50 text-gold-600 flex items-center justify-center mb-4 overflow-hidden">
            <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14L12 11m0 10V11m0 0L4 7m8 14l-8-4V7"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-gray-800">ATS</div>
        <div class="text-xs text-gold-600 mb-2">Asset Management System</div>
        <p class="text-sm text-gray-600 leading-relaxed">
            Physical + software asset register across every branch — assignment, lifecycle events, roster reconciliation. Scaffold, awaiting scope-of-work.
        </p>
        <div class="mt-4 text-xs text-gold-600 font-medium group-hover:translate-x-0.5 transition">Open ATS →</div>
    </a>

    {{-- Dialer — Smartping cloud telephony --}}
    <a href="{{ route('developer.dialer.home') }}"
       class="dev-card group block bg-white border border-gray-200 rounded-xl shadow-sm p-6 hover:border-brand-400">
        <div class="h-12 w-12 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center mb-4 overflow-hidden">
            <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>
        <div class="text-lg font-semibold text-gray-800">Dialer</div>
        <div class="text-xs text-brand-600 mb-2">Smartping cloud telephony · 1600 toll-free</div>
        <p class="text-sm text-gray-600 leading-relaxed">
            Inbound + outbound calling on Smartping. Customer database, CSV import, auto-created dialer tickets, call trail, missed calls and recording playback.
        </p>
        <div class="mt-4 text-xs text-brand-600 font-medium group-hover:translate-x-0.5 transition">Open Dialer →</div>
    </a>

</div>

<div class="mt-8 md:mt-10 bg-white border border-gray-200 rounded-lg shadow-sm p-5">
    <div class="flex items-center gap-2 text-sm text-gray-700 font-semibold mb-2">
        <svg width="16" height="16" class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a1 1 0 011 1v3a1 1 0 11-2 0V7a1 1 0 011-1zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        Heads up
    </div>
    <ul class="text-xs text-gray-500 space-y-1 list-disc list-inside">
        <li>ATS and Dialer are only visible under the developer role — admin / CISO / management / employees don't see them yet.</li>
        <li>CTS is the real system. Anything you do there is live data.</li>
        <li>Promote ATS / Dialer to the main app (sidebar, other roles) only after sign-off.</li>
    </ul>
</div>
@endsection
