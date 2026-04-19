@extends('layouts.app')
@section('title', 'Team Performance Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-brand-500 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-6">Team Performance</h2>
<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Resolver</th>
                <th class="px-4 py-3 text-left">Level</th>
                <th class="px-4 py-3 text-left">Region</th>
                <th class="px-4 py-3 text-right">Assigned</th>
                <th class="px-4 py-3 text-right">Resolved</th>
                <th class="px-4 py-3 text-right">TAT Violated</th>
                <th class="px-4 py-3 text-right">Resolution %</th>
                <th class="px-4 py-3 text-right text-green-600">&lt; 1d</th>
                <th class="px-4 py-3 text-right text-yellow-600">1-3d</th>
                <th class="px-4 py-3 text-right text-orange-600">3-7d</th>
                <th class="px-4 py-3 text-right text-red-600">&gt; 7d</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($engineers as $eng)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $eng->name }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $eng->resolver_level ? strtoupper($eng->resolver_level) : '—' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $eng->assignedRegion->name ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ $eng->total_assigned }}</td>
                <td class="px-4 py-3 text-right text-green-600 font-semibold">{{ $eng->resolved_count }}</td>
                <td class="px-4 py-3 text-right {{ $eng->violated_count > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $eng->violated_count }}</td>
                <td class="px-4 py-3 text-right text-gray-600">
                    {{ $eng->total_assigned > 0 ? round($eng->resolved_count / $eng->total_assigned * 100, 1) : 0 }}%
                </td>
                <td class="px-4 py-3 text-right">{{ $eng->aging_1d }}</td>
                <td class="px-4 py-3 text-right">{{ $eng->aging_1_3d }}</td>
                <td class="px-4 py-3 text-right">{{ $eng->aging_3_7d }}</td>
                <td class="px-4 py-3 text-right {{ $eng->aging_7d > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $eng->aging_7d }}</td>
            </tr>
            @empty
            <tr><td colspan="11" class="px-6 py-8 text-center text-gray-400">No team data yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
