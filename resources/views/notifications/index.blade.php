@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-700">Notifications</h2>
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:underline">Mark all as read</button>
        </form>
    </div>
    <div class="bg-white rounded-lg shadow divide-y">
        @forelse($notifications as $notif)
        <div class="p-4 {{ $notif->read_at ? 'opacity-60' : 'bg-indigo-50' }}">
            <div class="flex justify-between">
                <p class="text-sm font-medium text-gray-800">{{ $notif->data['message'] ?? 'Notification' }}</p>
                @if(!$notif->read_at)
                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Mark read</button>
                </form>
                @endif
            </div>
            @if(isset($notif->data['ticket_number']))
            <p class="text-xs text-gray-500 mt-1">Ticket: <a href="{{ route('tickets.show', $notif->data['ticket_id']) }}" class="text-indigo-600 hover:underline">{{ $notif->data['ticket_number'] }}</a></p>
            @endif
            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @empty
        <div class="p-8 text-center text-gray-400">No notifications.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
