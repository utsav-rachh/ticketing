@extends('layouts.developer')
@section('title', 'Sandbox Home')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl md:text-3xl font-bold text-white">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</h1>
    <p class="text-slate-400 text-sm mt-1">Pick a feature to prototype. Anything in this sandbox stays inside the developer view until it ships.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <a href="{{ route('developer.assets') }}"
       class="dev-card block bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6 hover:border-indigo-500">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-12 w-12 rounded-lg bg-indigo-500/20 text-indigo-300 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0v10l-8 4m8-14L12 11m0 10V11m0 0L4 7m8 14l-8-4V7"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-semibold text-white">Asset Management</div>
                <div class="text-xs text-slate-400">Physical + software assets — multi-branch, ~700 employees</div>
            </div>
        </div>
        <p class="text-sm text-slate-300 leading-relaxed">
            Track laptops, peripherals, licences and software entitlements across every branch. Assign assets to users,
            log lifecycle events (issued, returned, repaired, retired) and reconcile against HR / branch rosters.
        </p>
        <div class="mt-4 text-xs text-indigo-300 font-medium">Open prototype →</div>
    </a>

    <a href="{{ route('developer.dialer') }}"
       class="dev-card block bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-6 hover:border-emerald-500">
        <div class="flex items-center gap-3 mb-3">
            <div class="h-12 w-12 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.586a1 1 0 01.707.293L10 5h4l1.707-1.707A1 1 0 0116.414 3H19a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-semibold text-white">Customer-care Dialer</div>
                <div class="text-xs text-slate-400">Outbound queue + ticket sync</div>
            </div>
        </div>
        <p class="text-sm text-slate-300 leading-relaxed">
            Power-dial customer-care queues. Each call attaches to (or auto-creates) a ticket so disposition,
            recording link and follow-ups stay inside the existing ticketing flow.
        </p>
        <div class="mt-4 text-xs text-emerald-300 font-medium">Open prototype →</div>
    </a>
</div>

<div class="mt-8 bg-slate-800/50 border border-slate-700 rounded-lg p-5">
    <div class="flex items-center gap-2 text-sm text-slate-300 font-semibold mb-2">
        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a1 1 0 011 1v3a1 1 0 11-2 0V7a1 1 0 011-1zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        Sandbox rules
    </div>
    <ul class="text-xs text-slate-400 space-y-1 list-disc list-inside">
        <li>Nothing here is visible to admin, CISO, resolvers, management or employees yet.</li>
        <li>Promote a feature only after the scope-of-work is signed off and tests pass.</li>
        <li>Reuse existing models (User, Branch, Ticket) where possible — don't duplicate org data.</li>
    </ul>
</div>
@endsection
