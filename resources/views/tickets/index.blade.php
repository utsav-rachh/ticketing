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

<form method="GET" class="bg-white shadow rounded p-4 grid grid-cols-2 md:grid-cols-6 gap-3 mb-4 text-sm">
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
        <span class="text-xs font-medium text-gray-500">Region</span>
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
        <button type="submit" class="text-white px-4 py-1.5 rounded text-sm" style="background:#0056B3;">Filter</button>
        <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded text-sm">Clear</a>
    </div>
</form>

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
                <th class="px-4 py-3 text-left">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50
                {{ $ticket->is_red_flag ? 'bg-red-50/50' : '' }}
                {{ $ticket->is_tat_violated && !in_array($ticket->status,['resolved','closed']) ? 'bg-red-50' : '' }}">
                <td class="px-4 py-3">
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs">
                        @if($ticket->is_red_flag)<span class="text-red-600 mr-1" title="Red-flagged">●</span>@endif
                        {{ $ticket->ticket_number }}
                    </a>
                    @if($ticket->is_tat_violated && !in_array($ticket->status,['resolved','closed']))
                        <span class="ml-1 text-xs text-red-600 font-bold">TAT!</span>
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
                <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
</div>
@endsection
