@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    @foreach([
        ['Total','total','blue'],
        ['Open','open','blue'],
        ['Assigned','assigned','indigo'],
        ['In Progress','in_progress','yellow'],
        ['On Hold','hold','purple'],
        ['Resolved','resolved','green'],
        ['Closed','closed','gray'],
        ['Pending Info','pending_info','orange'],
        ['TAT Violated','violated','red'],
        ['Red-Flagged','red_flag','red'],
    ] as [$label,$key,$color])
    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-{{ $color }}-500">
        <div class="text-2xl font-bold text-gray-800">{{ $stats[$key] }}</div>
        <div class="text-xs text-gray-500">{{ $label }}</div>
    </div>
    @endforeach
</div>

@if($pendingExpenseCount !== null && $pendingExpenseCount > 0)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 flex items-center justify-between">
    <div>
        <span class="font-semibold">{{ $pendingExpenseCount }}</span> expense{{ $pendingExpenseCount === 1 ? '' : 's' }} pending your approval.
    </div>
    <a href="{{ route('expenses.approvals') }}" class="text-sm bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Review</a>
</div>
@endif

@if(!empty($byType))
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Tickets (last 30 days by type)</h3>
    <div class="flex items-end gap-6 h-32">
        @php $max = max($byType ?: [1]); @endphp
        @foreach(['application' => 'Application','infrastructure' => 'Infrastructure','admin' => 'Admin'] as $k => $label)
        <div class="flex flex-col items-center gap-1 flex-1">
            <div class="text-xs text-gray-500">{{ $byType[$k] ?? 0 }}</div>
            <div class="w-full max-w-[60px] bg-brand-500 rounded-t" style="height: {{ ($byType[$k] ?? 0) / $max * 100 }}%"></div>
            <div class="text-xs text-gray-500">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="font-semibold text-gray-700">Recent Tickets</h2>
        <a href="{{ route('tickets.create') }}" class="bg-brand-500 text-white text-sm px-4 py-2 rounded hover:bg-brand-600">+ New Ticket</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Ticket #</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Branch / Region</th>
                    <th class="px-4 py-3 text-left">Raised By</th>
                    <th class="px-4 py-3 text-left">Assignee</th>
                    <th class="px-4 py-3 text-left">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentTickets as $ticket)
                <tr class="hover:bg-gray-50 {{ $ticket->is_red_flag ? 'bg-red-50/50' : '' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs">
                            @if($ticket->is_red_flag)<span class="text-red-600 mr-1">●</span>@endif
                            {{ $ticket->ticket_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3 max-w-xs truncate">{{ $ticket->subject }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $ticket->branch->name ?? '—' }} / {{ $ticket->branch->region->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $ticket->creator->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $ticket->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No tickets yet. <a href="{{ route('tickets.create') }}" class="text-brand-500">Create one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
