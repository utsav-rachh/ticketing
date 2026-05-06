@extends('layouts.app')
@section('title', 'Projects')
@section('content')
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <h2 class="text-lg md:text-xl font-bold text-gray-700">Projects</h2>
    <a href="{{ route('projects.create') }}" class="text-white px-4 py-2 rounded text-sm font-medium btn-touch" style="background:#0056B3;">+ New Project</a>
</div>

<div class="mb-4 flex gap-2 text-sm overflow-x-auto pb-1 -mx-1 px-1">
    <a href="{{ route('projects.index') }}" class="flex-shrink-0 px-3 py-1.5 rounded {{ !$status ? 'bg-brand-500 text-white' : 'bg-white border border-gray-300 text-gray-600' }}">All</a>
    @foreach(['active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $v => $l)
    <a href="{{ route('projects.index', ['status' => $v]) }}" class="flex-shrink-0 px-3 py-1.5 rounded {{ $status === $v ? 'bg-brand-500 text-white' : 'bg-white border border-gray-300 text-gray-600' }}">{{ $l }}</a>
    @endforeach
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm" data-mobile="cards">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Number</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Owner</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Tickets</th>
                <th class="px-4 py-3 text-left">Dates</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($projects as $p)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs">
                    <a href="{{ route('projects.show', $p) }}" class="text-brand-600 hover:underline">{{ $p->number }}</a>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $p->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $p->owner->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @php
                        $color = ['active' => 'green', 'on_hold' => 'yellow', 'completed' => 'gray'][$p->status] ?? 'gray';
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $color }}-100 text-{{ $color }}-700">
                        {{ str_replace('_',' ', $p->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $p->tickets_count }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ optional($p->start_date)->format('d M Y') ?: '—' }} →
                    {{ optional($p->end_date)->format('d M Y') ?: '—' }}
                </td>
                <td class="px-4 py-3 text-right text-xs">
                    <a href="{{ route('projects.edit', $p) }}" class="text-brand-600 hover:underline">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No projects yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 md:px-6 py-3 md:py-4 border-t">{{ $projects->links() }}</div>
</div>
@endsection
