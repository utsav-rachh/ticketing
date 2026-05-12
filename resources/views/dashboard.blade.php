@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<style>
    /* Collapsible dashboard sections */
    details.dash-section > summary { list-style: none; cursor: pointer; }
    details.dash-section > summary::-webkit-details-marker { display: none; }
    details.dash-section > summary .dash-chevron { transition: transform 150ms ease; }
    details.dash-section[open] > summary .dash-chevron { transform: rotate(90deg); }
</style>

{{-- Circular stat tiles. Each is a colored ring + big count + label below. Clicking a tile opens the ticket list pre-filtered to that stat. --}}
@php
    $statTotal = max(1, (int) $stats['total']);
    $cardSpec = [
        ['Total',         'total',    '#0056B3', 100,                                                                              route('tickets.index')],
        ['Open / Active', 'open',     '#0EA5E9', $statTotal ? round($stats['open']     / $statTotal * 100) : 0, route('tickets.index', ['status_group' => 'open'])],
        ['On Hold',       'hold',     '#A855F7', $statTotal ? round($stats['hold']     / $statTotal * 100) : 0, route('tickets.index', ['status' => 'hold'])],
        ['Resolved',      'resolved', '#16A34A', $statTotal ? round($stats['resolved'] / $statTotal * 100) : 0, route('tickets.index', ['status_group' => 'resolved'])],
        ['TAT Violated',  'violated', '#DC2626', $statTotal ? round($stats['violated'] / $statTotal * 100) : 0, route('tickets.index', ['tat_violated' => 1])],
        ['Red-Flagged',   'red_flag', '#B91C1C', $statTotal ? round($stats['red_flag'] / $statTotal * 100) : 0, route('tickets.index', ['is_red_flag' => 1, 'active_only' => 1])],
    ];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 md:gap-4 mb-4 md:mb-6">
    @foreach($cardSpec as [$label, $key, $color, $pct, $url])
    @php $pct = max(0, min(100, (int) $pct)); @endphp
    <a href="{{ $url }}" class="bg-white rounded-xl shadow-sm py-3 md:py-5 px-2 md:px-3 flex flex-col items-center justify-center text-center hover:shadow-md transition-shadow focus:outline-none focus:ring-2 focus:ring-offset-1" style="--tw-ring-color: {{ $color }};">
        <div class="rounded-full flex items-center justify-center stat-ring"
             style="width: clamp(56px, 16vw, 80px); aspect-ratio: 1 / 1;
                    background: {{ $color }}10;
                    border: 4px solid {{ $color }};">
            <span class="text-xl md:text-2xl font-extrabold leading-none" style="color: {{ $color }};">{{ $stats[$key] }}</span>
        </div>
        <div class="mt-2 md:mt-3 text-[11px] md:text-xs font-semibold text-gray-700">{{ $label }}</div>
        <div class="text-[10px] text-gray-400 mt-0.5">
            {{ $key === 'total' ? 'all tickets' : $pct . '% of total' }}
        </div>
    </a>
    @endforeach
</div>

@if($projectStats !== null)
{{-- Projects summary — Admin / CISO only. --}}
@php
    $projectCards = [
        ['Projects',  'total',     '#0056B3', route('projects.index')],
        ['Active',    'active',    '#0EA5E9', route('projects.index', ['status' => 'active'])],
        ['On Hold',   'on_hold',   '#A855F7', route('projects.index', ['status' => 'on_hold'])],
        ['Completed', 'completed', '#16A34A', route('projects.index', ['status' => 'completed'])],
    ];
@endphp
<div class="bg-white rounded-lg shadow p-4 mb-4 md:mb-6">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            <h2 class="font-semibold text-gray-700">Projects</h2>
        </div>
        <a href="{{ route('projects.index') }}" class="text-xs text-brand-500 hover:underline">View all →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($projectCards as [$label, $key, $color, $url])
        <a href="{{ $url }}" class="rounded-lg border border-gray-100 hover:border-gray-200 hover:shadow-sm transition p-3 flex items-center gap-3">
            <span class="text-2xl font-extrabold leading-none" style="color: {{ $color }};">{{ $projectStats[$key] }}</span>
            <span class="text-xs font-semibold text-gray-600">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif

@if(auth()->user()->canManageProjects())
<div class="bg-white rounded-lg shadow p-4 mb-4 md:mb-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="text-sm font-semibold text-gray-700">Quick create</div>
            <div class="text-xs text-gray-500">Start a new ticket or set up a project workspace.</div>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <a href="{{ route('tickets.create') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 text-white px-4 py-2 rounded text-sm font-medium btn-touch" style="background:#0056B3;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Ticket
            </a>
            <a href="{{ route('projects.create') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-white border border-brand-500 text-brand-600 px-4 py-2 rounded text-sm font-medium hover:bg-brand-50 btn-touch">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                New Project
            </a>
        </div>
    </div>
</div>
@endif

@if($pendingExpenseCount !== null && $pendingExpenseCount > 0)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 flex items-center justify-between">
    <div>
        <span class="font-semibold">{{ $pendingExpenseCount }}</span> expense{{ $pendingExpenseCount === 1 ? '' : 's' }} pending your approval.
    </div>
    <a href="{{ route('expenses.approvals') }}" class="text-sm bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Review</a>
</div>
@endif

{{-- Management Tickets — pinned at top, full op set (assign dropdown, TAT progress, etc). --}}
<details id="dash-management" class="dash-section bg-white rounded-lg shadow mb-4 md:mb-6 border-t-4 border-red-500" open>
    <summary class="px-4 md:px-6 py-3 md:py-4 border-b flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-2 min-w-0">
            <svg class="dash-chevron w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14l-7-3-7 3V3z"/></svg>
            <h2 class="font-semibold text-gray-700">Management Tickets</h2>
            <span class="text-xs text-gray-500 hidden sm:inline">— red-flagged & top priority</span>
        </div>
        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-semibold">{{ $managementTickets->count() }}</span>
    </summary>
    <div class="overflow-x-auto">
        @include('partials.dashboard-ticket-table', [
            'tickets'         => $managementTickets,
            'canQuickAssign'  => $canQuickAssign,
            'assignableUsers' => $assignableUsers,
            'emptyMessage'    => '<div class="text-gray-500 text-sm font-medium mb-1">No management tickets right now</div><div class="text-gray-400 text-xs">Top-priority tickets raised or red-flagged by management will appear here automatically.</div>',
        ])
    </div>
</details>

@if($projectStats !== null)
{{-- Project Tickets — tickets linked to a project (Admin / CISO only). --}}
<details id="dash-projects" class="dash-section bg-white rounded-lg shadow mb-4 md:mb-6 border-t-4 border-brand-500" open>
    <summary class="px-4 md:px-6 py-3 md:py-4 border-b flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-2 min-w-0">
            <svg class="dash-chevron w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <svg class="w-5 h-5 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
            <h2 class="font-semibold text-gray-700">Project Tickets</h2>
            <span class="text-xs text-gray-500 hidden sm:inline">— open tickets linked to a project</span>
        </div>
        <span class="text-xs px-2 py-0.5 bg-brand-50 text-brand-600 rounded-full font-semibold">{{ $projectTickets->count() }}</span>
    </summary>
    <div class="overflow-x-auto">
        @include('partials.dashboard-ticket-table', [
            'tickets'         => $projectTickets,
            'canQuickAssign'  => $canQuickAssign,
            'assignableUsers' => $assignableUsers,
            'withProject'     => true,
            'emptyMessage'    => 'No project-linked tickets are open right now. <a href="'.route('projects.index').'" class="text-brand-500">Browse projects</a>.',
        ])
    </div>
</details>
@endif

{{-- Recent Tickets — excludes anything already shown above. --}}
<details id="dash-recent" class="dash-section bg-white rounded-lg shadow" open>
    <summary class="px-4 md:px-6 py-3 md:py-4 border-b flex items-center justify-between gap-2 flex-wrap">
        <div class="flex items-center gap-2 min-w-0">
            <svg class="dash-chevron w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <h2 class="font-semibold text-gray-700">Recent Tickets</h2>
        </div>
        <a href="{{ route('tickets.create') }}" onclick="event.stopPropagation()" class="bg-brand-500 text-white text-sm px-3 py-2 rounded hover:bg-brand-600 btn-touch">+ New Ticket</a>
    </summary>
    <div class="overflow-x-auto">
        @include('partials.dashboard-ticket-table', [
            'tickets'         => $recentTickets,
            'canQuickAssign'  => $canQuickAssign,
            'assignableUsers' => $assignableUsers,
            'emptyMessage'    => 'No tickets yet. <a href="'.route('tickets.create').'" class="text-brand-500">Create one</a>.',
        ])
    </div>
</details>

<script>
(function () {
    // Remember which dashboard sections the user collapsed.
    document.querySelectorAll('details.dash-section').forEach(function (d) {
        var key = 'dashboard.section.' + d.id;
        var saved = localStorage.getItem(key);
        if (saved === 'closed') d.open = false;
        if (saved === 'open')   d.open = true;
        d.addEventListener('toggle', function () {
            localStorage.setItem(key, d.open ? 'open' : 'closed');
        });
    });
})();
</script>
@endsection
