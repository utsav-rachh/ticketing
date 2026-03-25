@extends('layouts.app')
@section('title', 'Expense Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-indigo-600 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-6">Expense Report</h2>
<div class="mb-6 bg-white rounded-lg shadow p-4 border-l-4 border-green-500 inline-block">
    <div class="text-2xl font-bold text-gray-800">&#8377;{{ number_format($grandTotal, 2) }}</div>
    <div class="text-sm text-gray-500">Total Expenses</div>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
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
