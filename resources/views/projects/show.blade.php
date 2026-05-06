@extends('layouts.app')
@section('title', $project->number . ' · ' . $project->name)
@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-4">
        <div class="min-w-0">
            <div class="text-xs text-gray-500 font-mono">{{ $project->number }}</div>
            <h2 class="text-lg md:text-xl font-bold text-gray-800 break-words">{{ $project->name }}</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tickets.create', ['project_id' => $project->id]) }}"
               class="text-white px-3 md:px-4 py-2 rounded text-sm font-medium btn-touch flex-1 md:flex-none text-center" style="background:#0056B3;">+ Create ticket</a>
            <a href="{{ route('projects.edit', $project) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded text-sm btn-touch text-center">Edit</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Owner</div>
            <div class="text-sm font-medium text-gray-800 mt-1">{{ $project->owner->name ?? '—' }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Status</div>
            @php $color = ['active' => 'green', 'on_hold' => 'yellow', 'completed' => 'gray'][$project->status] ?? 'gray'; @endphp
            <div class="mt-1">
                <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $color }}-100 text-{{ $color }}-700">
                    {{ str_replace('_',' ', $project->status) }}
                </span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-xs text-gray-500 uppercase">Dates</div>
            <div class="text-sm font-medium text-gray-800 mt-1">
                {{ optional($project->start_date)->format('d M Y') ?: '—' }}
                <span class="text-gray-400">→</span>
                {{ optional($project->end_date)->format('d M Y') ?: '—' }}
            </div>
        </div>
    </div>

    @if($project->description)
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="text-xs text-gray-500 uppercase mb-2">Description</div>
        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $project->description }}</div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b text-sm font-semibold text-gray-700">Tickets ({{ $tickets->total() }})</div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm" data-mobile="cards">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Number</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Priority</th>
                    <th class="px-4 py-3 text-left">Assignee</th>
                    <th class="px-4 py-3 text-left">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $t)
                <tr class="hover:bg-gray-50 {{ $t->is_red_flag ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs">
                        <a href="{{ route('tickets.show', $t) }}" class="text-brand-600 hover:underline">{{ $t->ticket_number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-800">{{ $t->subject }}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="px-2 py-0.5 rounded-full bg-{{ $t->status_color }}-100 text-{{ $t->status_color }}-700">{{ str_replace('_',' ', $t->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <span class="px-2 py-0.5 rounded-full bg-{{ $t->priority_color }}-100 text-{{ $t->priority_color }}-700">{{ ucfirst($t->priority) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->assignee->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $t->created_at?->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No tickets in this project yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-t">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection
