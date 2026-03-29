@extends('layouts.app')
@section('title', 'Team Performance Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-6">Team Performance Report</h2>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Engineer</th>
                <th class="px-6 py-3 text-left">Role</th>
                <th class="px-6 py-3 text-right">Assigned</th>
                <th class="px-6 py-3 text-right">Resolved</th>
                <th class="px-6 py-3 text-right">TAT Violated</th>
                <th class="px-6 py-3 text-right">Resolution %</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($engineers as $eng)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $eng->name }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-brand-100 text-brand-700">{{ str_replace('_',' ', $eng->role) }}</span>
                </td>
                <td class="px-6 py-3 text-right text-gray-700">{{ $eng->total_assigned }}</td>
                <td class="px-6 py-3 text-right text-green-600 font-semibold">{{ $eng->resolved_count }}</td>
                <td class="px-6 py-3 text-right {{ $eng->violated_count > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $eng->violated_count }}</td>
                <td class="px-6 py-3 text-right text-gray-600">
                    {{ $eng->total_assigned > 0 ? round($eng->resolved_count / $eng->total_assigned * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No team data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
