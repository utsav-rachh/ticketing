@extends('layouts.developer')
@section('title', 'Dialer')
@section('content')

@include('developer.dialer._subnav')

<div class="flex items-start justify-between flex-wrap gap-3 mb-6">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Dialer</h1>
        <p class="text-gray-500 text-sm mt-1">Smartping cloud telephony · 1600 toll-free. Calls sync to dialer tickets in this system.</p>
    </div>
    <span class="text-xs px-2.5 py-1 rounded-full {{ $configured ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
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
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <div class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</div>
        <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $value }}</div>
        <div class="text-[11px] text-gray-400 mt-1">{{ $hint }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Smartping iframe (or placeholder) --}}
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 text-sm font-semibold text-gray-800">Soft-phone</div>
        @if($iframeUrl)
            <iframe src="{{ $iframeUrl }}" class="w-full" style="height: 560px; border: 0;"
                    allow="microphone; autoplay" referrerpolicy="no-referrer"></iframe>
        @else
            <div class="p-8 text-center text-gray-500 text-sm">
                <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                    <svg width="24" height="24" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                Smartping hasn't provided the iframe URL yet.<br>
                Set <code class="text-gray-700 bg-gray-100 px-1 py-0.5 rounded">SMARTPING_IFRAME_URL</code> once they do — it embeds here.
            </div>
        @endif
    </div>

    {{-- Fallback Click-to-Call --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <div class="text-sm font-semibold text-gray-800 mb-1">Click-to-Call</div>
        <p class="text-xs text-gray-500 mb-4">Fallback path — Smartping rings your phone, then the customer. A dialer ticket is created.</p>
        <form method="POST" action="{{ route('developer.dialer.call') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Your phone (agent number)</label>
                <input name="agent_number" required placeholder="98xxxxxxxx" value="{{ old('agent_number', auth()->user()->phone) }}"
                       class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm placeholder-gray-400">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Customer</label>
                <select name="customer_id" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">— pick a customer —</option>
                    @foreach(\App\Models\DialerCustomer::orderBy('name')->limit(500)->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} · {{ $c->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-center text-[11px] text-gray-400">or</div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Phone number (ad-hoc)</label>
                <input name="customer_phone" placeholder="customer 10-digit number"
                       class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm placeholder-gray-400">
            </div>
            <button class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium rounded px-3 py-2">Call</button>
        </form>
    </div>
</div>

{{-- Recent calls --}}
<div class="mt-6 bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <div class="text-sm font-semibold text-gray-800">Recent calls</div>
        <a href="{{ route('developer.dialer.tickets.index') }}" class="text-xs text-brand-600 hover:underline">View full call log →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-gray-400 border-b border-gray-200 bg-gray-50">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($recentCalls as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><a href="{{ route('developer.dialer.tickets.show', $t) }}" class="text-brand-600 hover:underline">{{ $t->ticket_number }}</a></td>
                    <td class="px-4 py-2 text-gray-600 capitalize">{{ $t->direction }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $t->customer_name ?? $t->customer?->name ?? '—' }} <span class="text-gray-400">{{ $t->customer_phone }}</span></td>
                    <td class="px-4 py-2">@include('developer.dialer._status', ['status' => $t->call_status])</td>
                    <td class="px-4 py-2 text-gray-600">{{ $t->durationLabel() }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $t->agent_name ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-400">{{ $t->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No calls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
