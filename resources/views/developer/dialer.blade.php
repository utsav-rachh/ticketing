@extends('layouts.developer')
@section('title', 'Customer-care Dialer')
@section('content')

<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-white">Customer-care Dialer — Prototype</h1>
        <p class="text-slate-400 text-sm mt-1">Working scope: outbound dialer that syncs every call to a ticket in this system.</p>
    </div>
    <a href="{{ route('developer.home') }}" class="text-xs text-slate-400 hover:text-white">← Back to sandbox</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['Calls today', '0', 'Connected outbound calls'],
        ['Avg handle time', '—', 'Talk + wrap'],
        ['Tickets created', '0', 'From dialer dispositions'],
    ] as [$label, $value, $hint])
    <div class="bg-slate-800/70 border border-slate-700 rounded-lg p-4">
        <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
        <div class="text-2xl font-extrabold text-white mt-1">{{ $value }}</div>
        <div class="text-[11px] text-slate-500 mt-1">{{ $hint }}</div>
    </div>
    @endforeach
</div>

<div class="bg-slate-800/70 border border-slate-700 rounded-lg p-5 mb-6">
    <h2 class="text-sm font-semibold text-white mb-3">Planned modules</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-300">
        <div class="border border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-white">Campaigns + queues</div>
            <ul class="mt-2 text-xs text-slate-400 list-disc list-inside space-y-1">
                <li>Upload contact lists (CSV / API import)</li>
                <li>Per-campaign caller-ID, retry policy, working hours</li>
                <li>Agent assignment + barge / whisper</li>
            </ul>
        </div>
        <div class="border border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-white">Soft-phone agent panel</div>
            <ul class="mt-2 text-xs text-slate-400 list-disc list-inside space-y-1">
                <li>Click-to-call, hold, transfer, conference</li>
                <li>Live customer screen-pop with ticket history</li>
                <li>Disposition + notes captured per call</li>
            </ul>
        </div>
        <div class="border border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-white">Ticket sync</div>
            <ul class="mt-2 text-xs text-slate-400 list-disc list-inside space-y-1">
                <li>Each call attaches to (or creates) a ticket</li>
                <li>Recording link saved on the ticket timeline</li>
                <li>Follow-up call → reopens / extends the ticket</li>
            </ul>
        </div>
        <div class="border border-slate-700 rounded-lg p-4">
            <div class="font-semibold text-white">Reporting</div>
            <ul class="mt-2 text-xs text-slate-400 list-disc list-inside space-y-1">
                <li>Agent / queue / campaign performance dashboards</li>
                <li>SLA breaches surfaced into the existing TAT model</li>
                <li>Read-only export for ops + audit</li>
            </ul>
        </div>
    </div>
</div>

<div class="bg-slate-900/50 border border-dashed border-slate-700 rounded-lg p-5 text-center text-slate-400 text-sm">
    No data yet — once you share the dialer SoW (vendor / SIP provider / DB schema) I'll scaffold migrations, services and views.
</div>

@endsection
