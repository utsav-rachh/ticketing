@extends('layouts.app')
@section('title', 'All Tickets')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-gray-700">Tickets</h2>
    <div class="flex items-center gap-2">
        @if(auth()->user()->canExport())
        <a href="{{ route('tickets.export', request()->query()) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 text-sm">Export Excel</a>
        @endif
        <a href="{{ route('tickets.create') }}" class="bg-brand-500 text-white px-4 py-2 rounded hover:bg-brand-600 text-sm">+ New Ticket</a>
    </div>
</div>

@php
    $activeFilters = collect(['status','support_type','priority','region_id','from','to','is_red_flag'])
        ->filter(fn ($k) => request()->filled($k))->count();
@endphp
<details class="bg-white shadow rounded mb-4" {{ $activeFilters > 0 ? 'open' : '' }}>
    <summary class="cursor-pointer px-4 py-2.5 text-sm font-medium text-gray-700 flex items-center justify-between select-none">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filters
            @if($activeFilters > 0)
                <span class="bg-brand-500 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full">{{ $activeFilters }} active</span>
            @endif
        </span>
        <svg class="w-4 h-4 text-gray-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <form method="GET" class="px-4 pb-4 pt-2 grid grid-cols-2 md:grid-cols-6 gap-3 text-sm border-t border-gray-100">
        <label>
            <span class="text-xs font-medium text-gray-500">Status</span>
            <select name="status" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">— all —</option>
                @foreach(['open','assigned','in_progress','pending_info','hold','resolved','closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="text-xs font-medium text-gray-500">Type</span>
            <select name="support_type" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">— all —</option>
                @foreach(['application','infrastructure','admin'] as $t)
                <option value="{{ $t }}" {{ request('support_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="text-xs font-medium text-gray-500">Priority</span>
            <select name="priority" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">— all —</option>
                @foreach(['critical','high','medium','low'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="text-xs font-medium text-gray-500">State</span>
            <select name="region_id" class="w-full border border-gray-300 rounded px-2 py-1.5">
                <option value="">— all —</option>
                @foreach(\App\Models\Region::active()->orderBy('name')->get() as $r)
                <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="text-xs font-medium text-gray-500">From</span>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </label>
        <label>
            <span class="text-xs font-medium text-gray-500">To</span>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded px-2 py-1.5">
        </label>
        <div class="md:col-span-6 flex items-center gap-2">
            <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                <input type="checkbox" name="is_red_flag" value="1" {{ request('is_red_flag') ? 'checked' : '' }}>
                Red-flagged only
            </label>
            <button type="submit" class="text-white px-4 py-1.5 rounded text-sm" style="background:#0056B3;">Apply</button>
            <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded text-sm">Clear</a>
        </div>
    </form>
</details>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Ticket #</th>
                <th class="px-4 py-3 text-left">Subject</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Priority</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Branch</th>
                <th class="px-4 py-3 text-left">Assigned To</th>
                <th class="px-4 py-3 text-left">TAT</th>
                <th class="px-4 py-3 text-left">Aging</th>
                <th class="px-4 py-3 text-left">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            @php
                $isViolated = $ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']);
            @endphp
            <tr class="{{ $isViolated ? 'bg-red-100 hover:bg-red-200 border-l-4 border-red-500' : ($ticket->is_red_flag ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50') }}">
                <td class="px-4 py-3">
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs inline-flex items-center gap-1">
                        @if($ticket->is_red_flag)
                        <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20" title="Red-flagged"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
                        @endif
                        {{ $ticket->ticket_number }}
                    </a>
                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $ticket->created_at?->format('d M Y') }}</div>
                    @if($isViolated)
                        <span class="mt-1 inline-block bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">TAT VIOLATED</span>
                    @endif
                </td>
                <td class="px-4 py-3 max-w-xs truncate">{{ $ticket->subject }}</td>
                <td class="px-4 py-3 capitalize text-gray-500 text-xs">{{ $ticket->support_type }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $ticket->branch->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->tat_deadline?->format('d M, H:i') }}</td>
                <td class="px-4 py-3 text-xs">
                    <span class="font-semibold text-gray-700">{{ $ticket->aging_human }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-6 py-8 text-center text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
</div>
@endsection
