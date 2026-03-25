@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<h2 class="text-xl font-bold text-gray-700 mb-6">Reports</h2>
<div class="grid grid-cols-2 gap-4">
    @foreach([
        ['Priority Distribution', 'reports.priority', 'View ticket counts by priority level'],
        ['TAT Compliance', 'reports.tat', 'SLA compliance and violation summary'],
        ['Expense Report', 'reports.expenses', 'Expense tracking by date'],
        ['Team Performance', 'reports.team', 'Engineer workload and resolution stats'],
    ] as [$title, $route, $desc])
    <a href="{{ route($route) }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="font-semibold text-gray-800 mb-1">{{ $title }}</h3>
        <p class="text-sm text-gray-500">{{ $desc }}</p>
    </a>
    @endforeach
</div>
@endsection
