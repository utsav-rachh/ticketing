@extends('layouts.app')
@section('title', $user->name . "'s Tickets")
@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('team.index') }}" class="text-indigo-600 hover:underline text-sm">&larr; Team</a>
    <h2 class="text-xl font-bold text-gray-700">{{ $user->name }}'s Tickets</h2>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-6 py-3 text-left">Ticket #</th>
                <th class="px-6 py-3 text-left">Subject</th>
                <th class="px-6 py-3 text-left">Priority</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tickets as $ticket)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3"><a href="{{ route('tickets.show', $ticket) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $ticket->ticket_number }}</a></td>
                <td class="px-6 py-3 max-w-xs truncate">{{ $ticket->subject }}</td>
                <td class="px-6 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">{{ ucfirst($ticket->priority) }}</span></td>
                <td class="px-6 py-3"><span class="text-xs text-gray-600">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span></td>
                <td class="px-6 py-3 text-xs text-gray-400">{{ $ticket->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No tickets.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $tickets->links() }}</div>
</div>
@endsection
