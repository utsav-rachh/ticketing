@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['Total','total','border-blue-500'],
        ['Open','open','border-sky-500'],
        ['Assigned','assigned','border-indigo-500'],
        ['In Progress','in_progress','border-yellow-500'],
        ['On Hold','hold','border-purple-500'],
        ['Resolved','resolved','border-green-500'],
        ['Closed','closed','border-gray-400'],
        ['Pending Info','pending_info','border-orange-500'],
        ['TAT Violated','violated','border-red-500'],
        ['Red-Flagged','red_flag','border-red-600'],
    ] as [$label,$key,$borderClass])
    <div class="bg-white rounded-lg shadow-sm px-3 py-2.5 border-l-4 {{ $borderClass }} flex flex-col justify-center">
        <div class="text-xl font-bold text-gray-800 leading-tight">{{ $stats[$key] }}</div>
        <div class="text-[11px] text-gray-500 mt-0.5">{{ $label }}</div>
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
<div class="bg-white rounded-lg shadow px-4 py-3 mb-6">
    <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Tickets — last 30 days by type</h3>
    @php $max = max($byType ?: [1]); @endphp
    <div class="space-y-1.5">
        @foreach(['application' => 'Application','infrastructure' => 'Infrastructure','admin' => 'Admin / HR'] as $k => $label)
        @php $count = $byType[$k] ?? 0; @endphp
        <div class="flex items-center gap-3 text-xs">
            <div class="w-28 text-gray-600">{{ $label }}</div>
            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-brand-500 rounded-full" style="width: {{ $max > 0 ? ($count / $max * 100) : 0 }}%"></div>
            </div>
            <div class="w-6 text-right font-semibold text-gray-800">{{ $count }}</div>
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
                    <th class="px-4 py-3 text-left">Branch / State</th>
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
                    <td class="px-4 py-3 text-gray-600">
                        @if($canQuickAssign && !in_array($ticket->status, ['resolved','closed']) && $assignableUsers->isNotEmpty())
                        <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex items-center gap-1">
                            @csrf
                            <select name="assigned_to" class="border border-gray-300 rounded px-1.5 py-1 text-xs bg-white w-32" onchange="if(this.value) this.form.submit()">
                                <option value="" disabled {{ $ticket->assigned_to ? '' : 'selected' }}>— Assign —</option>
                                @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}" {{ $ticket->assigned_to == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                        @else
                            {{ $ticket->assignee->name ?? 'Unassigned' }}
                        @endif
                    </td>
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
