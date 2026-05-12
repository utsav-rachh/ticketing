@extends('layouts.developer')
@section('title', 'Dialer')
@section('content')

@include('developer.dialer._subnav')

@if(session('error'))
<div class="mb-4 px-4 py-2 rounded bg-red-500/10 border border-red-500/30 text-red-300 text-sm">{{ session('error') }}</div>
@endif

<div class="flex items-start justify-between flex-wrap gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Dialer</h1>
        <p class="text-slate-400 text-sm mt-1">Smartping cloud telephony · 1600 toll-free. Calls sync to dialer tickets in this system.</p>
    </div>
    <span class="text-xs px-2.5 py-1 rounded-full {{ $configured ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
        {{ $configured ? 'Smartping connected' : 'Smartping not configured' }}
    </span>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Calls today',     $stats['calls_today'],     'Inbound + outbound'],
        ['Connected today', $stats['connected_today'], 'Status = completed'],
        ['Missed calls',    $stats['missed_open'],     'All time'],
        ['Customers',       $stats['customers'],       'In the dialer database'],
    ] as [$label, $value, $hint])
    <div class="bg-slate-800/70 border border-slate-700 rounded-lg p-4">
        <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
        <div class="text-2xl font-extrabold text-white mt-1">{{ $value }}</div>
        <div class="text-[11px] text-slate-500 mt-1">{{ $hint }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Smartping iframe (or placeholder) --}}
    <div class="lg:col-span-2 bg-slate-800/70 border border-slate-700 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700 text-sm font-semibold text-white">Soft-phone</div>
        @if($iframeUrl)
            <iframe src="{{ $iframeUrl }}" class="w-full" style="height: 560px; border: 0;"
                    allow="microphone; autoplay" referrerpolicy="no-referrer"></iframe>
        @else
            <div class="p-8 text-center text-slate-400 text-sm">
                <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-slate-700/60 flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                Smartping hasn't provided the iframe URL yet.<br>
                Set <code class="text-slate-300">SMARTPING_IFRAME_URL</code> once they do — it embeds here.
            </div>
        @endif
    </div>

    {{-- Fallback Click-to-Call --}}
    <div class="bg-slate-800/70 border border-slate-700 rounded-lg p-5">
        <div class="text-sm font-semibold text-white mb-1">Click-to-Call</div>
        <p class="text-xs text-slate-400 mb-4">Fallback path — Smartping rings your phone, then the customer. A dialer ticket is created.</p>
        <form method="POST" action="{{ route('developer.dialer.call') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Your phone (agent number)</label>
                <input name="agent_number" required placeholder="98xxxxxxxx" value="{{ old('agent_number', auth()->user()->phone) }}"
                       class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white placeholder-slate-600">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Customer</label>
                <select name="customer_id" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
                    <option value="">— pick a customer —</option>
                    @foreach(\App\Models\DialerCustomer::orderBy('name')->limit(500)->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} · {{ $c->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-center text-[11px] text-slate-500">or</div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Phone number (ad-hoc)</label>
                <input name="customer_phone" placeholder="customer 10-digit number"
                       class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white placeholder-slate-600">
            </div>
            <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded px-3 py-2">Call</button>
        </form>
    </div>
</div>

{{-- Recent calls --}}
<div class="mt-6 bg-slate-800/70 border border-slate-700 rounded-lg">
    <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
        <div class="text-sm font-semibold text-white">Recent calls</div>
        <a href="{{ route('developer.dialer.tickets.index') }}" class="text-xs text-emerald-300 hover:text-emerald-200">View full call log →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-700">
                <tr>
                    <th class="px-4 py-2">Ticket</th>
                    <th class="px-4 py-2">Direction</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Duration</th>
                    <th class="px-4 py-2">Agent</th>
                    <th class="px-4 py-2">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($recentCalls as $t)
                <tr class="hover:bg-slate-700/30">
                    <td class="px-4 py-2"><a href="{{ route('developer.dialer.tickets.show', $t) }}" class="text-emerald-300 hover:underline">{{ $t->ticket_number }}</a></td>
                    <td class="px-4 py-2 text-slate-300 capitalize">{{ $t->direction }}</td>
                    <td class="px-4 py-2 text-slate-300">{{ $t->customer_name ?? $t->customer?->name ?? '—' }} <span class="text-slate-500">{{ $t->customer_phone }}</span></td>
                    <td class="px-4 py-2">@include('developer.dialer._status', ['status' => $t->call_status])</td>
                    <td class="px-4 py-2 text-slate-300">{{ $t->durationLabel() }}</td>
                    <td class="px-4 py-2 text-slate-300">{{ $t->agent_name ?? '—' }}</td>
                    <td class="px-4 py-2 text-slate-400">{{ $t->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No calls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
