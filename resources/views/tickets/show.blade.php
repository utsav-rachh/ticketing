@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('content')
<div class="grid grid-cols-3 gap-6">
    <!-- Main -->
    <div class="col-span-2 space-y-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-mono text-sm text-gray-500">{{ $ticket->ticket_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                        @if($ticket->is_tat_violated)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 font-bold">TAT VIOLATED</span>
                        @endif
                    </div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $ticket->subject }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $ticket->category->name }} &rarr; {{ $ticket->subcategory->name }} &bull; {{ ucfirst($ticket->support_type) }}</p>
                </div>
            </div>
            @if($ticket->description)
            <div class="mt-4 p-4 bg-gray-50 rounded text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
            @endif
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Activity Timeline</h3>
            <div class="space-y-3">
                @foreach($ticket->activities as $act)
                <div class="flex gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($act->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">{{ $act->user->name ?? 'System' }}</span>
                        <span class="text-gray-500 ml-1">{{ $act->description }}</span>
                        <div class="text-xs text-gray-400">{{ $act->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            @can('update', $ticket)
            <form method="POST" action="{{ route('tickets.activity', $ticket) }}" class="mt-4 flex gap-2">
                @csrf
                <input type="text" name="description" required placeholder="Add a note..."
                    class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">Add Note</button>
            </form>
            @endcan
        </div>

        <!-- Expenses -->
        @can('update', $ticket)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Expenses</h3>
            @forelse($ticket->expenses as $exp)
            <div class="flex justify-between text-sm py-2 border-b last:border-0">
                <span class="text-gray-700">{{ $exp->description }}</span>
                <span class="font-semibold text-gray-800">&#8377;{{ number_format($exp->amount, 2) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No expenses recorded.</p>
            @endforelse
            <form method="POST" action="{{ route('tickets.expense', $ticket) }}" class="mt-4 grid grid-cols-3 gap-2">
                @csrf
                <input type="text" name="description" required placeholder="Description" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <input type="number" name="amount" required placeholder="Amount (&#8377;)" min="0" step="0.01" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <button type="submit" class="col-span-3 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Add Expense</button>
            </form>
        </div>
        @endcan
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Details -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Ticket Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Raised By</dt><dd class="font-medium">{{ $ticket->creator->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Assigned To</dt><dd class="font-medium">{{ $ticket->assignee->name ?? 'Unassigned' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd class="text-gray-600">{{ $ticket->created_at->format('d M Y, H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">TAT Deadline</dt>
                    <dd class="{{ $ticket->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }}">{{ $ticket->tat_deadline->format('d M Y, H:i') }}</dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">TAT Progress</dt>
                    <dd>
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $ticket->tatProgress() >= 100 ? 'bg-red-500' : ($ticket->tatProgress() >= 75 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                style="width: {{ min(100, $ticket->tatProgress()) }}%"></div>
                        </div>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Update Status -->
        @can('update', $ticket)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Update Status</h3>
            <form method="POST" action="{{ route('tickets.status', $ticket) }}" class="space-y-2">
                @csrf @method('PATCH')
                <select name="status" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    @foreach(['open','assigned','in_progress','pending_info','resolved','closed'] as $s)
                    <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded text-sm hover:bg-indigo-700">Update Status</button>
            </form>
        </div>
        @endcan

        <!-- Assign -->
        @can('assign', $ticket)
        @if(count($assignableUsers) > 0)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Assign Ticket</h3>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                @csrf
                <select name="assigned_to" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Select engineer...</option>
                    @foreach($assignableUsers as $u)
                    <option value="{{ $u['id'] }}" {{ $ticket->assigned_to == $u['id'] ? 'selected' : '' }}>{{ $u['name'] }} ({{ $u['role'] }})</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">Assign</button>
            </form>
        </div>
        @endif
        @endcan

        <!-- Attachments -->
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Attachments</h3>
            @forelse($ticket->attachments as $att)
            <div class="flex items-center gap-2 py-1 text-sm">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <a href="{{ Storage::url($att->file_path) }}" class="text-indigo-600 hover:underline truncate" target="_blank">{{ $att->file_name }}</a>
            </div>
            @empty
            <p class="text-xs text-gray-400">No attachments.</p>
            @endforelse
            <form method="POST" action="{{ route('tickets.attachment', $ticket) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <input type="file" name="attachment" class="text-sm text-gray-500 mb-2 block">
                <button type="submit" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">Upload</button>
            </form>
        </div>
    </div>
</div>
@endsection
