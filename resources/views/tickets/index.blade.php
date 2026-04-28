@extends('layouts.app')
@section('title', 'All Tickets')
@section('content')

{{-- Inline filter bar (no collapsible, no "filter" label). --}}
<form method="GET" class="bg-white shadow-sm rounded mb-4 px-3 py-2 flex flex-wrap items-end gap-2 text-sm">
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">Status</span>
        <select name="status" class="border border-gray-300 rounded px-2 py-1 text-xs w-32">
            <option value="">All</option>
            @foreach(['open','assigned','in_progress','pending_info','hold','resolved','closed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_',' ',$s) }}</option>
            @endforeach
        </select>
    </label>
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">Type</span>
        <select name="support_type" class="border border-gray-300 rounded px-2 py-1 text-xs w-32">
            <option value="">All</option>
            @foreach(['application','infrastructure','admin'] as $t)
            <option value="{{ $t }}" {{ request('support_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </label>
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">Priority</span>
        <select name="priority" class="border border-gray-300 rounded px-2 py-1 text-xs w-28">
            <option value="">All</option>
            @foreach(['critical','high','medium','low'] as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </label>
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">State</span>
        <select name="region_id" class="border border-gray-300 rounded px-2 py-1 text-xs w-36">
            <option value="">All</option>
            @foreach(\App\Models\Region::active()->orderBy('name')->get() as $r)
            <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">From</span>
        <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded px-2 py-1 text-xs">
    </label>
    <label class="flex flex-col">
        <span class="text-[10px] uppercase font-medium text-gray-500 ml-0.5">To</span>
        <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded px-2 py-1 text-xs">
    </label>
    <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 mb-1 ml-1">
        <input type="checkbox" name="is_red_flag" value="1" {{ request('is_red_flag') ? 'checked' : '' }}>
        Red-flagged
    </label>
    <div class="flex items-center gap-2 ml-auto">
        <button type="submit" class="text-white px-3 py-1.5 rounded text-xs font-medium" style="background:#0056B3;">Apply</button>
        <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded text-xs">Clear</a>

        {{-- Column visibility dropdown — preferences saved per user in localStorage. --}}
        <div class="relative" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="bg-white border border-gray-300 text-gray-600 px-3 py-1.5 rounded text-xs flex items-center gap-1 hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Columns
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute right-0 top-full mt-1 z-20 bg-white border border-gray-200 rounded shadow-lg w-44 py-1 text-xs">
                @php
                    $cols = [
                        'ticket' => 'Ticket #',
                        'subject' => 'Subject',
                        'type' => 'Type',
                        'priority' => 'Priority',
                        'status' => 'Status',
                        'branch' => 'Branch',
                        'assignee' => 'Assigned To',
                        'tat' => 'TAT',
                        'aging' => 'Aging',
                        'created' => 'Created',
                    ];
                @endphp
                @foreach($cols as $key => $label)
                <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" class="ticket-col-toggle" data-col="{{ $key }}" checked>
                    <span class="text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        @if(auth()->user()->canExport())
        <a href="{{ route('tickets.export', request()->query()) }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-xs">Export Excel</a>
        @endif
        <a href="{{ route('tickets.create') }}" class="bg-brand-500 text-white px-3 py-1.5 rounded text-xs hover:bg-brand-600">+ New Ticket</a>
    </div>
</form>

@php
    $sortLink = function ($column) use ($sort, $dir) {
        $isCurrent = $sort === $column;
        $newDir    = $isCurrent && $dir === 'asc' ? 'desc' : 'asc';
        $params    = array_merge(request()->query(), ['sort' => $column, 'dir' => $newDir]);
        return [
            'href'    => route('tickets.index') . '?' . http_build_query($params),
            'current' => $isCurrent,
            'arrow'   => $isCurrent ? ($dir === 'asc' ? '▲' : '▼') : '',
        ];
    };
@endphp

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm" id="ticketsTable">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                @php $s = $sortLink('ticket_number'); @endphp
                <th class="px-4 py-3 text-left col-ticket"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Ticket # {{ $s['arrow'] }}</a></th>
                @php $s = $sortLink('subject'); @endphp
                <th class="px-4 py-3 text-left col-subject"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Subject {{ $s['arrow'] }}</a></th>
                @php $s = $sortLink('support_type'); @endphp
                <th class="px-4 py-3 text-left col-type"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Type {{ $s['arrow'] }}</a></th>
                @php $s = $sortLink('priority'); @endphp
                <th class="px-4 py-3 text-left col-priority"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Priority {{ $s['arrow'] }}</a></th>
                @php $s = $sortLink('status'); @endphp
                <th class="px-4 py-3 text-left col-status"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Status {{ $s['arrow'] }}</a></th>
                <th class="px-4 py-3 text-left col-branch">Branch</th>
                <th class="px-4 py-3 text-left col-assignee">Assigned To</th>
                @php $s = $sortLink('tat_deadline'); @endphp
                <th class="px-4 py-3 text-left col-tat"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">TAT {{ $s['arrow'] }}</a></th>
                <th class="px-4 py-3 text-left col-aging">Aging</th>
                @php $s = $sortLink('created_at'); @endphp
                <th class="px-4 py-3 text-left col-created"><a href="{{ $s['href'] }}" class="hover:text-gray-800 {{ $s['current'] ? 'text-gray-800 font-bold' : '' }}">Created {{ $s['arrow'] }}</a></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            @php
                $isViolated = $ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']);
            @endphp
            <tr class="{{ $isViolated ? 'bg-red-100 hover:bg-red-200 border-l-4 border-red-500' : ($ticket->is_red_flag ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50') }}">
                <td class="px-4 py-3 col-ticket">
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs inline-flex items-center gap-1">
                        @if($ticket->is_red_flag)
                        <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20" title="Red-flagged"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
                        @endif
                        {{ $ticket->ticket_number }}
                    </a>
                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $ticket->created_at?->format('d M Y') }}</div>
                    @if($isViolated)
                    <div class="mt-1"><span class="bg-red-600 text-white text-[8px] font-bold px-1 py-0.5 rounded">TAT VIOLATED</span></div>
                    @endif
                </td>
                <td class="px-4 py-3 max-w-xs truncate col-subject">{{ $ticket->subject }}</td>
                <td class="px-4 py-3 capitalize text-gray-500 text-xs col-type">{{ $ticket->support_type }}</td>
                <td class="px-4 py-3 col-priority">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td class="px-4 py-3 col-status">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600 col-branch">{{ $ticket->branch->name ?? '—' }}</td>
                <td class="px-4 py-3 text-gray-600 col-assignee">{{ $ticket->assignee->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-3 text-xs text-gray-400 col-tat">{{ $ticket->tat_deadline?->format('d M, H:i') }}</td>
                <td class="px-4 py-3 text-xs col-aging">
                    <span class="font-semibold text-gray-700">{{ $ticket->aging_human }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400 col-created">{{ $ticket->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="px-6 py-8 text-center text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
</div>

<script>
(function () {
    // Column visibility toggles, persisted across reloads.
    const STORAGE_KEY = 'ticketing.tickets.cols';
    const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    const toggles = document.querySelectorAll('.ticket-col-toggle');

    const apply = (col, visible) => {
        document.querySelectorAll('.col-' + col).forEach(el => {
            el.style.display = visible ? '' : 'none';
        });
    };

    toggles.forEach(t => {
        const col = t.dataset.col;
        if (stored[col] === false) { t.checked = false; apply(col, false); }
        t.addEventListener('change', () => {
            stored[col] = t.checked;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(stored));
            apply(col, t.checked);
        });
    });
})();
</script>
@endsection
