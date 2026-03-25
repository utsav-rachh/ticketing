@extends('layouts.app')
@section('title', 'Priority Distribution Report')
@section('content')
<a href="{{ route('reports.index') }}" class="text-indigo-600 hover:underline text-sm mb-4 block">&larr; Reports</a>
<h2 class="text-xl font-bold text-gray-700 mb-6">Priority Distribution Report</h2>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Priority</th>
                <th class="px-6 py-3 text-right">Total Tickets</th>
                <th class="px-6 py-3 text-right">Share</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $total = $data->sum('total'); @endphp
            @forelse($data as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $row->priority === 'critical' ? 'bg-red-100 text-red-700' : ($row->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($row->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                        {{ ucfirst($row->priority) }}
                    </span>
                </td>
                <td class="px-6 py-3 text-right font-semibold">{{ $row->total }}</td>
                <td class="px-6 py-3 text-right text-gray-500">
                    @if($total > 0)
                        {{ round($row->total / $total * 100, 1) }}%
                        <div class="w-32 bg-gray-200 rounded-full h-1.5 mt-1 ml-auto">
                            <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ round($row->total / $total * 100) }}%"></div>
                        </div>
                    @else
                        0%
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No ticket data yet.</td></tr>
            @endforelse
        </tbody>
        @if($total > 0)
        <tfoot class="bg-gray-50">
            <tr>
                <td class="px-6 py-3 font-semibold text-gray-700">Total</td>
                <td class="px-6 py-3 text-right font-bold text-gray-800">{{ $total }}</td>
                <td class="px-6 py-3 text-right text-gray-500">100%</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
