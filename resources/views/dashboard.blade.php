@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    @foreach([['Total','total','blue'],['Open','open','blue'],['In Progress','in_progress','yellow'],['Resolved','resolved','green'],['TAT Violated','violated','red']] as [$label,$key,$color])
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-{{ $color }}-500">
        <div class="text-2xl font-bold text-gray-800">{{ $stats[$key] }}</div>
        <div class="text-sm text-gray-500">{{ $label }}</div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h2 class="font-semibold text-gray-700">Recent Tickets</h2>
        <a href="{{ route('tickets.create') }}" class="bg-brand-500 text-white text-sm px-4 py-2 rounded hover:bg-brand-600">+ New Ticket</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Ticket #</th>
                    <th class="px-6 py-3 text-left">Subject</th>
                    <th class="px-6 py-3 text-left">Priority</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Raised By</th>
                    <th class="px-6 py-3 text-left">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentTickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs">{{ $ticket->ticket_number }}</a>
                    </td>
                    <td class="px-6 py-3 max-w-xs truncate">{{ $ticket->subject }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $ticket->creator->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-400 text-xs">{{ $ticket->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No tickets yet. <a href="{{ route('tickets.create') }}" class="text-brand-500">Create one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
