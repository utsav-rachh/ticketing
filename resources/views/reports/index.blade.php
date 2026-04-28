@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<p class="text-sm text-gray-500 mb-6">All reports support Excel export and can be filtered by state where applicable.</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach([
        ['Priority Distribution', 'reports.priority', 'Ticket counts grouped by priority level.'],
        ['TAT Compliance',        'reports.tat',      'SLA compliance summary with per-priority breakdown.'],
        ['Expense Report',        'reports.expenses', 'Approved / pending / rejected totals plus daily trend.'],
        ['Team Performance',      'reports.team',     'Per-resolver workload, resolution rate, and aging buckets.'],
        ['Ticket Aging',          'reports.aging',    'Open tickets bucketed by how long they have been outstanding.'],
    ] as [$title, $route, $desc])
    <a href="{{ route($route) }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow border-l-4 border-brand-500">
        <h3 class="font-semibold text-gray-800 mb-1">{{ $title }}</h3>
        <p class="text-sm text-gray-500">{{ $desc }}</p>
        <div class="mt-3 text-[11px] text-brand-500 font-medium">Open report &rarr;</div>
    </a>
    @endforeach
</div>
@endsection
