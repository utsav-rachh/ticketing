@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

{{-- Donut-ring stat cards. Each ring is a share of the total (or fixed 100% for Total). --}}
@php
    $statTotal = max(1, (int) $stats['total']);
    $cardSpec = [
        ['Total',         'total',    '#0056B3', 100],
        ['Open / Active', 'open',     '#0EA5E9', $statTotal ? round($stats['open']     / $statTotal * 100) : 0],
        ['On Hold',       'hold',     '#A855F7', $statTotal ? round($stats['hold']     / $statTotal * 100) : 0],
        ['Resolved',      'resolved', '#16A34A', $statTotal ? round($stats['resolved'] / $statTotal * 100) : 0],
        ['TAT Violated',  'violated', '#DC2626', $statTotal ? round($stats['violated'] / $statTotal * 100) : 0],
        ['Red-Flagged',   'red_flag', '#B91C1C', $statTotal ? round($stats['red_flag'] / $statTotal * 100) : 0],
    ];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-6">
    @foreach($cardSpec as [$label, $key, $color, $pct])
    @php $pct = max(0, min(100, (int) $pct)); @endphp
    <div class="bg-white rounded-lg shadow-sm py-4 px-3 flex flex-col items-center justify-start text-center">
        <div class="relative flex-shrink-0" style="width: 72px; height: 72px;">
            <svg viewBox="0 0 36 36" style="width: 72px; height: 72px; transform: rotate(-90deg); display: block;">
                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#F3F4F6" stroke-width="3"></circle>
                <circle cx="18" cy="18" r="15.915" fill="none"
                        stroke="{{ $color }}" stroke-width="3" stroke-linecap="round"
                        stroke-dasharray="{{ $pct }}, 100"></circle>
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-2xl font-bold leading-none" style="color: {{ $color }};">{{ $stats[$key] }}</span>
            </div>
        </div>
        <div class="mt-3 text-[11px] font-semibold text-gray-700 leading-tight">{{ $label }}</div>
        @if($key !== 'total')
        <div class="text-[10px] text-gray-400 mt-0.5">{{ $pct }}% of total</div>
        @else
        <div class="text-[10px] text-gray-400 mt-0.5">all tickets</div>
        @endif
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

{{-- Management Tickets — same layout as Recent Tickets, pinned at the top --}}
<div class="bg-white rounded-lg shadow mb-6 border-t-4 border-red-500">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14l-7-3-7 3V3z"/></svg>
            <h2 class="font-semibold text-gray-700">Management Tickets</h2>
            <span class="text-xs text-gray-500">— red-flagged & top priority</span>
        </div>
        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-semibold">{{ $managementTickets->count() }}</span>
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
                    <th class="px-4 py-3 text-left">Age</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($managementTickets as $ticket)
                @php $mIsViolated = $ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']); @endphp
                <tr class="{{ $mIsViolated ? 'bg-red-100 hover:bg-red-200 border-l-4 border-red-600' : 'bg-red-50 hover:bg-red-100' }}">
                    <td class="px-4 py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20" title="Red-flagged"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
                            {{ $ticket->ticket_number }}
                        </a>
                        @if($mIsViolated)
                            <span class="ml-1 inline-block bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">TAT VIOLATED</span>
                        @endif
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
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center">
                        <div class="text-gray-500 text-sm font-medium mb-1">No management tickets right now</div>
                        <div class="text-gray-400 text-xs">Top-priority tickets raised or red-flagged by management will appear here automatically.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Tickets — extended details (category, issue type, age, TAT, contact) --}}
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
                    <th class="px-4 py-3 text-left">Subject / Issue</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Branch / State</th>
                    <th class="px-4 py-3 text-left">Raised By</th>
                    <th class="px-4 py-3 text-left">Assignee</th>
                    <th class="px-4 py-3 text-left">TAT</th>
                    <th class="px-4 py-3 text-left">Age</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentTickets as $ticket)
                @php
                    $isViolated = $ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']);
                @endphp
                <tr class="{{ $isViolated ? 'bg-red-100 hover:bg-red-200 border-l-4 border-red-500' : ($ticket->is_red_flag ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50') }}">
                    <td class="px-4 py-3 align-top">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs inline-flex items-center gap-1">
                            @if($ticket->is_red_flag)
                            <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20" title="Red-flagged"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
                            @endif
                            {{ $ticket->ticket_number }}
                        </a>
                        @if($isViolated)
                            <span class="ml-1 inline-block bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">TAT VIOLATED</span>
                        @endif
                        <div class="text-[10px] text-gray-400 mt-0.5 uppercase">{{ ucfirst($ticket->support_type) }}</div>
                    </td>
                    <td class="px-4 py-3 align-top max-w-xs">
                        <div class="truncate font-medium text-gray-800">{{ $ticket->subject }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ $ticket->subcategory->name ?? ($ticket->custom_issue ?? '—') }}</div>
                    </td>
                    <td class="px-4 py-3 align-top text-xs text-gray-600">
                        {{ $ticket->category->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 align-top">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-top">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 align-top text-xs text-gray-600">
                        {{ $ticket->branch->name ?? '—' }}<br>
                        <span class="text-gray-400">{{ $ticket->branch->region->name ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 align-top text-gray-600">
                        <div class="text-xs">{{ $ticket->creator->name ?? '—' }}</div>
                        <div class="text-[10px] text-gray-400">{{ $ticket->employee_contact_phone ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 align-top text-gray-600">
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
                            <span class="text-xs">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-top text-xs">
                        @if(in_array($ticket->status, ['resolved','closed']))
                            <span class="text-green-600">✓ done</span>
                        @elseif($ticket->isOverdue())
                            <span class="text-red-600 font-semibold">Violated</span>
                        @else
                            @php $p = $ticket->tatProgress(); @endphp
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 {{ $p >= 75 ? 'bg-yellow-500' : 'bg-green-500' }}" style="width: {{ $p }}%"></div>
                            </div>
                            <span class="text-[10px] text-gray-500">{{ $p }}%</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-top text-gray-400 text-xs">{{ $ticket->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-6 py-8 text-center text-gray-400">No tickets yet. <a href="{{ route('tickets.create') }}" class="text-brand-500">Create one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
