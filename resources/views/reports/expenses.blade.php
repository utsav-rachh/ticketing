@extends('layouts.app')
@section('title', 'Expense Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-4">Expense Report</h2>

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
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="text-2xl font-bold text-gray-800">&#8377;{{ number_format($approvedTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Approved</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
        <div class="text-2xl font-bold text-gray-800">&#8377;{{ number_format($pendingTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Pending</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
        <div class="text-2xl font-bold text-gray-800">&#8377;{{ number_format($rejectedTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Rejected</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b text-sm font-semibold text-gray-700">Daily totals (last 30 days)</div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-right">Daily Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($data as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-gray-700">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                <td class="px-6 py-3 text-right font-semibold text-gray-800">&#8377;{{ number_format($row->total, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="px-6 py-8 text-center text-gray-400">No expenses recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
