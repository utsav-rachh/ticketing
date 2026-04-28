@extends('layouts.app')
@section('title', 'Ticket Aging Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">Ticket Aging</h2>
    <a href="{{ route('reports.aging', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
       class="bg-brand-500 text-white text-sm px-4 py-2 rounded hover:bg-brand-600">Export Excel</a>
</div>

<form method="GET" class="bg-white shadow rounded p-3 flex items-end gap-3 mb-6">
    <label class="block flex-1 max-w-xs">
        <span class="text-xs font-medium text-gray-500">State</span>
        <select name="region_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— all states —</option>
            @foreach($regions as $r)
            <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
        </select>
    </label>
    <button class="text-white px-4 py-2 rounded text-sm" style="background:#0056B3;">Apply</button>
    <a href="{{ route('reports.aging') }}" class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm">Clear</a>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="text-2xl font-bold">{{ $buckets['lt_1d'] }}</div><div class="text-sm text-gray-500">&lt; 1 day</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
        <div class="text-2xl font-bold">{{ $buckets['d1_3'] }}</div><div class="text-sm text-gray-500">1 – 3 days</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
        <div class="text-2xl font-bold">{{ $buckets['d3_7'] }}</div><div class="text-sm text-gray-500">3 – 7 days</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <div class="text-2xl font-bold">{{ $buckets['gt_7d'] }}</div><div class="text-sm text-gray-500">&gt; 7 days</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Ticket #</th>
                <th class="px-4 py-3 text-left">Subject</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Priority</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Branch / State</th>
                <th class="px-4 py-3 text-left">Assignee</th>
                <th class="px-4 py-3 text-right">Aging</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $t)
            @php $days = (int) optional($t->created_at)->diffInDays(now()); @endphp
            <tr class="{{ $days >= 7 ? 'bg-red-50' : ($days >= 3 ? 'bg-orange-50' : '') }} hover:bg-gray-50">
                <td class="px-4 py-3"><a href="{{ route('tickets.show', $t) }}" class="text-brand-500 hover:underline font-mono text-xs">{{ $t->ticket_number }}</a></td>
                <td class="px-4 py-3 max-w-xs truncate">{{ $t->subject }}</td>
                <td class="px-4 py-3 capitalize text-xs text-gray-500">{{ $t->support_type }}</td>
                <td class="px-4 py-3 capitalize text-xs">{{ $t->priority }}</td>
                <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_',' ',$t->status)) }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $t->branch->name ?? '—' }}<br>
                    <span class="text-gray-400">{{ $t->branch->region->name ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-xs">{{ $t->assignee->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-3 text-right text-xs font-semibold {{ $days >= 7 ? 'text-red-600' : ($days >= 3 ? 'text-orange-600' : 'text-gray-700') }}">{{ $t->aging_human }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No open tickets.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
