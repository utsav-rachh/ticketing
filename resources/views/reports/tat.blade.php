@extends('layouts.app')
@section('title', 'TAT Compliance Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">TAT Compliance</h2>
    <a href="{{ route('reports.tat', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
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
    <a href="{{ route('reports.tat') }}" class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm">Clear</a>
</form>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <div class="text-2xl font-bold">{{ $total }}</div><div class="text-sm text-gray-500">Total Tickets</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="text-2xl font-bold">{{ $onTime }}</div><div class="text-sm text-gray-500">On Time</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <div class="text-2xl font-bold">{{ $violated }}</div><div class="text-sm text-gray-500">TAT Violated</div>
    </div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-3 border-b text-sm font-semibold text-gray-700">By Priority</div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr><th class="px-4 py-3 text-left">Priority</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Violated</th><th class="px-4 py-3 text-right">Compliance %</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($byPriority as $row)
            <tr>
                <td class="px-4 py-3 capitalize font-medium">{{ $row->priority }}</td>
                <td class="px-4 py-3 text-right">{{ $row->total }}</td>
                <td class="px-4 py-3 text-right text-red-600">{{ $row->violated }}</td>
                <td class="px-4 py-3 text-right">{{ $row->total > 0 ? round((1 - $row->violated/$row->total)*100, 1) : 100 }}%</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No ticket data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
