@extends('layouts.developer')
@section('title', 'Dialer · Call log')
@section('content')

@include('developer.dialer._subnav')

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold text-white">{{ $status === 'missed' ? 'Missed calls' : 'Call log' }}</h1>
        <p class="text-slate-400 text-sm mt-1">{{ $tickets->total() }} dialer ticket{{ $tickets->total() === 1 ? '' : 's' }}.</p>
    </div>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2 items-center">
    <input name="q" value="{{ $q }}" placeholder="Ticket / customer / phone / agent…"
           class="w-full md:w-80 bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white placeholder-slate-600">
    <select name="direction" class="bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
        <option value="">Any direction</option>
        @foreach(\App\Models\DialerTicket::DIRECTIONS as $d)
            <option value="{{ $d }}" @selected($direction === $d)>{{ ucfirst($d) }}</option>
        @endforeach
    </select>
    <select name="status" class="bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
        <option value="">Any status</option>
        @foreach(\App\Models\DialerTicket::STATUSES as $s)
            <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="bg-slate-700 hover:bg-slate-600 text-white text-sm rounded px-3 py-2">Filter</button>
    @if($q || $direction || $status)
    <a href="{{ route('developer.dialer.tickets.index') }}" class="text-xs text-slate-400 hover:text-white">Clear</a>
    @endif
</form>

<div class="bg-slate-800/70 border border-slate-700 rounded-lg overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-700">
            <tr>
                <th class="px-4 py-2">Ticket</th>
                <th class="px-4 py-2">Direction</th>
                <th class="px-4 py-2">Customer</th>
                <th class="px-4 py-2">Phone</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Duration</th>
                <th class="px-4 py-2">Recording</th>
                <th class="px-4 py-2">Agent</th>
                <th class="px-4 py-2">When</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700/60">
            @forelse($tickets as $t)
            <tr class="hover:bg-slate-700/30">
                <td class="px-4 py-2"><a href="{{ route('developer.dialer.tickets.show', $t) }}" class="text-emerald-300 hover:underline">{{ $t->ticket_number }}</a></td>
                <td class="px-4 py-2 text-slate-300 capitalize">{{ $t->direction }}</td>
                <td class="px-4 py-2 text-slate-300">{{ $t->customer_name ?? $t->customer?->name ?? '—' }}</td>
                <td class="px-4 py-2 text-slate-300">{{ $t->customer_phone ?: '—' }}</td>
                <td class="px-4 py-2">@include('developer.dialer._status', ['status' => $t->call_status])</td>
                <td class="px-4 py-2 text-slate-300">{{ $t->durationLabel() }}</td>
                <td class="px-4 py-2">{!! $t->hasRecording() ? '<span class="text-emerald-300 text-xs">●&nbsp;available</span>' : '<span class="text-slate-600 text-xs">—</span>' !!}</td>
                <td class="px-4 py-2 text-slate-300">{{ $t->agent_name ?: '—' }}</td>
                <td class="px-4 py-2 text-slate-400">{{ $t->created_at->format('d M, H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">No calls match.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $tickets->links() }}</div>
@endsection
