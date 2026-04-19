@extends('layouts.app')
@section('title', 'TAT Compliance Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-4">TAT Compliance Report</h2>
<form method="GET" class="bg-white shadow rounded p-4 flex items-end gap-3 mb-6">
    <label class="block flex-1 max-w-xs">
        <span class="text-xs font-medium text-gray-500">Region</span>
        <select name="region_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— all regions —</option>
            @foreach($regions as $r)
            <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
        </select>
    </label>
    <button class="text-white px-4 py-2 rounded text-sm" style="background:#0056B3;">Filter</button>
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
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold text-gray-700 mb-4">By Priority</h3>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr><th class="px-4 py-3 text-left">Priority</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Violated</th><th class="px-4 py-3 text-right">Compliance %</th></tr>
        </thead>
        <tbody class="divide-y">
            @foreach($byPriority as $row)
            <tr>
                <td class="px-4 py-3 capitalize font-medium">{{ $row->priority }}</td>
                <td class="px-4 py-3 text-right">{{ $row->total }}</td>
                <td class="px-4 py-3 text-right text-red-600">{{ $row->violated }}</td>
                <td class="px-4 py-3 text-right">{{ $row->total > 0 ? round((1 - $row->violated/$row->total)*100, 1) : 100 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
