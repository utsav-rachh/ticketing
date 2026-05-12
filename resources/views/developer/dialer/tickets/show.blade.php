@extends('layouts.developer')
@section('title', 'Dialer · '.$ticket->ticket_number)
@section('content')

@include('developer.dialer._subnav')

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">{{ $ticket->ticket_number }}</h1>
        <p class="text-gray-500 text-sm mt-1 flex items-center gap-2 flex-wrap">
            <span class="capitalize">{{ $ticket->direction }} call</span> ·
            @include('developer.dialer._status', ['status' => $ticket->call_status])
            · {{ $ticket->created_at->format('d M Y, H:i') }}
        </p>
    </div>
    <div class="flex items-center gap-2">
        @if($ticket->isLive())
        <form method="POST" action="{{ route('developer.dialer.hangup', $ticket) }}">
            @csrf
            <button class="text-sm px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white">End call</button>
        </form>
        @endif
        <a href="{{ route('developer.dialer.tickets.index') }}" class="text-xs text-gray-500 hover:text-brand-600">← Call log</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        {{-- Recording --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <div class="text-sm font-semibold text-gray-800 mb-3">Recording</div>
            @if($ticket->hasRecording())
                <audio controls preload="none" class="w-full">
                    <source src="{{ $ticket->recording_url }}">
                    Your browser can’t play this recording.
                </audio>
                <div class="mt-2 text-[11px] text-gray-400 break-all">{{ $ticket->recording_url }}</div>
            @else
                <p class="text-sm text-gray-400">No recording yet. Smartping pushes the URL once the call ends and the file is ready.</p>
            @endif
        </div>

        {{-- Notes --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <div class="text-sm font-semibold text-gray-800 mb-3">Notes</div>
            <form method="POST" action="{{ route('developer.dialer.tickets.notes', $ticket) }}">
                @csrf @method('PATCH')
                <textarea name="notes" rows="4" class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm">{{ old('notes', $ticket->notes) }}</textarea>
                <div class="mt-2 text-right">
                    <button class="text-sm px-3 py-1.5 rounded bg-brand-500 hover:bg-brand-600 text-white">Save notes</button>
                </div>
            </form>
        </div>

        {{-- Call trail --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-5 py-3 border-b border-gray-200 text-sm font-semibold text-gray-800">Call trail</div>
            <ul class="divide-y divide-gray-100">
                @forelse($ticket->logs as $log)
                <li class="px-5 py-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-700">{{ str_replace('_', ' ', $log->event) }}</span>
                        <span class="text-[11px] text-gray-400">{{ $log->created_at->format('d M, H:i:s') }}</span>
                    </div>
                    @if(!empty($log->data))
                    <pre class="mt-2 text-[11px] text-gray-500 bg-gray-50 border border-gray-100 rounded p-2 overflow-x-auto">{{ json_encode($log->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    @endif
                </li>
                @empty
                <li class="px-5 py-6 text-center text-gray-400 text-sm">No events logged.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Side: details --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 self-start">
        <div class="text-sm font-semibold text-gray-800 mb-3">Details</div>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Customer</dt><dd class="text-gray-800 text-right">{{ $ticket->customer_name ?? $ticket->customer?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Phone</dt><dd class="text-gray-800 text-right">{{ $ticket->customer_phone ?: '—' }}</dd></div>
            @if($ticket->customer)
            <div class="flex justify-between gap-3"><dt class="text-gray-500">In DB</dt><dd class="text-right"><a href="{{ route('developer.dialer.customers.index', ['q' => $ticket->customer->phone]) }}" class="text-brand-600 hover:underline">view</a></dd></div>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Direction</dt><dd class="text-gray-800 text-right capitalize">{{ $ticket->direction }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Status</dt><dd class="text-right">@include('developer.dialer._status', ['status' => $ticket->call_status])</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Duration</dt><dd class="text-gray-800 text-right">{{ $ticket->durationLabel() }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Agent</dt><dd class="text-gray-800 text-right">{{ $ticket->agent_name ?: '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Smartping ID</dt><dd class="text-gray-800 text-right break-all">{{ $ticket->smartping_call_id ?: '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-gray-500">Created</dt><dd class="text-gray-800 text-right">{{ $ticket->created_at->format('d M Y, H:i') }}</dd></div>
        </dl>
    </div>
</div>
@endsection
