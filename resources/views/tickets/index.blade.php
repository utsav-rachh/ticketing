@extends('layouts.app')
@section('title', 'All Tickets')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-gray-700">Tickets</h2>
    <a href="{{ route('tickets.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">+ New Ticket</a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Ticket #</th>
                <th class="px-6 py-3 text-left">Subject</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-left">Priority</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Assigned To</th>
                <th class="px-6 py-3 text-left">TAT</th>
                <th class="px-6 py-3 text-left">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50 {{ $ticket->is_tat_violated && !in_array($ticket->status,['resolved','closed']) ? 'bg-red-50' : '' }}">
                <td class="px-6 py-3">
                    <a href="{{ route('tickets.show', $ticket) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $ticket->ticket_number }}</a>
                    @if($ticket->is_tat_violated && !in_array($ticket->status,['resolved','closed']))
                        <span class="ml-1 text-xs text-red-600 font-bold">TAT!</span>
                    @endif
                </td>
                <td class="px-6 py-3 max-w-xs truncate">{{ $ticket->subject }}</td>
                <td class="px-6 py-3 capitalize text-gray-500 text-xs">{{ $ticket->support_type }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </td>
                <td class="px-6 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                </td>
                <td class="px-6 py-3 text-gray-600">{{ $ticket->assignee->name ?? '—' }}</td>
                <td class="px-6 py-3 text-xs text-gray-400">{{ $ticket->tat_deadline->format('d M, H:i') }}</td>
                <td class="px-6 py-3 text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No tickets found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
</div>
@endsection
