@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main -->
    <div class="lg:col-span-2 space-y-6">

        @if($ticket->is_red_flag)
        <div class="flex items-center gap-3 bg-red-50 border-2 border-red-300 text-red-800 rounded-lg p-4">
            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
            <div>
                <div class="font-semibold">Red Flag — Management ticket</div>
                <div class="text-sm">Treat as highest priority. Escalate immediately if blocked.</div>
            </div>
        </div>
        @endif

        @if($ticket->isOnHold())
        <div class="flex items-center gap-3 bg-purple-50 border border-purple-200 text-purple-800 rounded-lg p-4">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <div class="font-semibold">On Hold since {{ $ticket->hold_started_at?->format('d M Y, H:i') }}</div>
                <div class="text-sm">TAT clock is paused until the ticket is moved off hold.</div>
            </div>
        </div>
        @endif

        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
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
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $ticket->category->name }} &rarr; {{ $ticket->subcategory->name }}
                        @if($ticket->custom_issue) (<em class="text-gray-600">{{ $ticket->custom_issue }}</em>) @endif
                        &bull; {{ ucfirst($ticket->support_type) }}
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    @if(auth()->user()->canExport())
                    <a href="{{ route('tickets.pdf', $ticket) }}"
                       class="text-xs px-3 py-1.5 rounded inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l4 4h4a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Export PDF
                    </a>
                    @endif
                    @can('toggleRedFlag', $ticket)
                    <form method="POST" action="{{ route('tickets.redflag', $ticket) }}">
                        @csrf
                        <button type="submit" class="w-full text-xs px-3 py-1.5 rounded {{ $ticket->is_red_flag ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $ticket->is_red_flag ? 'Clear red flag' : 'Red-flag ticket' }}
                        </button>
                    </form>
                    @endcan
                </div>
            </div>

            @if($ticket->description)
            <div class="mt-4 p-4 bg-gray-50 rounded text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
            @endif
        </div>

        <!-- Unified Timeline -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Activity Timeline</h3>

            @can('comment', $ticket)
            <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data" class="mb-6 border border-gray-200 rounded-lg p-4 bg-gray-50 space-y-3">
                @csrf
                @can('updateStatus', $ticket)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <select name="status" class="border border-gray-300 rounded px-3 py-2 text-sm bg-white">
                        <option value="">— keep status —</option>
                        @foreach(['open','assigned','in_progress','pending_info','resolved','closed'] as $s)
                        <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                        @can('hold', $ticket)
                        <option value="hold">Hold</option>
                        @endcan
                    </select>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Attachments (multiple allowed)</label>
                        <input type="file" name="attachments[]" multiple class="text-sm text-gray-500 w-full">
                    </div>
                </div>
                @else
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Attachments (multiple allowed)</label>
                    <input type="file" name="attachments[]" multiple class="text-sm text-gray-500">
                </div>
                @endcan
                <textarea name="note" rows="2" placeholder="Add a note, update, or context…"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"></textarea>
                @error('note') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('status') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex justify-end">
                    <button type="submit" class="bg-brand-500 text-white px-4 py-2 rounded text-sm hover:bg-brand-600">Post update</button>
                </div>
            </form>
            @endcan

            <div class="space-y-1">
                @forelse($timeline as $item)
                <div class="flex gap-3 text-sm py-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($item->user->name ?? 'S', 0, 1)) }}
                        </div>
                        @if(!$loop->last)
                        <div class="w-px flex-1 bg-gray-200 mt-1"></div>
                        @endif
                    </div>
                    <div class="flex-1 pb-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-gray-700">{{ $item->user->name ?? 'System' }}</span>
                            @if($item->old || $item->new)
                            <span class="text-gray-500 text-xs">
                                changed status:
                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">{{ $item->old ?: '—' }}</span>
                                &rarr;
                                <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-medium">{{ $item->new ?: '—' }}</span>
                            </span>
                            @endif
                            <span class="text-xs text-gray-400 ml-auto">{{ $item->at?->format('d M Y, H:i') }}</span>
                        </div>
                        @if($item->text)
                        <div class="text-gray-700 mt-1 whitespace-pre-wrap">{{ $item->text }}</div>
                        @endif

                        {{-- Inline attachment previews --}}
                        @if($item->attachments->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($item->attachments as $att)
                            @if($att->isImage())
                            <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="block">
                                <img src="{{ Storage::url($att->file_path) }}"
                                     alt="{{ $att->file_name }}"
                                     class="h-24 w-24 object-cover rounded border border-gray-200 hover:opacity-90 transition">
                            </a>
                            @else
                            <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                               class="flex items-center gap-1.5 px-3 py-1.5 rounded border border-gray-200 bg-gray-50 hover:bg-gray-100 text-xs text-gray-700 transition max-w-xs">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="truncate">{{ $att->file_name }}</span>
                            </a>
                            @endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400">No activity yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Expenses (infrastructure + admin only) -->
        @if($ticket->support_type !== 'application')
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Expenses</h3>
            @forelse($ticket->expenses as $exp)
            <div class="flex justify-between items-center text-sm py-2 border-b last:border-0">
                <div>
                    <div class="text-gray-700">{{ $exp->description }}</div>
                    <div class="text-xs text-gray-400">
                        {{ $exp->expense_date->format('d M Y') }} · by {{ $exp->addedBy->name ?? '' }} ·
                        <span class="uppercase font-medium
                            {{ $exp->status === 'approved' ? 'text-green-600' : ($exp->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ $exp->status }}
                        </span>
                        @if($exp->invoice_path)
                        · <a href="{{ Storage::url($exp->invoice_path) }}" target="_blank" class="text-brand-500 hover:underline">invoice</a>
                        @endif
                    </div>
                </div>
                <span class="font-semibold text-gray-800">&#8377;{{ number_format($exp->amount, 2) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No expenses recorded.</p>
            @endforelse

            @can('addExpense', $ticket)
            <form method="POST" action="{{ route('tickets.expense', $ticket) }}" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-2">
                @csrf
                <input type="text" name="description" required placeholder="Description" class="border border-gray-300 rounded px-3 py-2 text-sm md:col-span-2">
                <input type="number" name="amount" required placeholder="Amount (&#8377;)" min="0" step="0.01" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <input type="file" name="invoice" required class="text-sm text-gray-500 md:col-span-3">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">Submit for approval</button>
            </form>
            @endcan
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Ticket Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Raised By</dt><dd class="font-medium text-right">{{ $ticket->creator->name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Branch</dt><dd class="font-medium text-right">{{ $ticket->branch->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">State</dt><dd class="font-medium text-right">{{ $ticket->branch->region->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Assigned To</dt><dd class="font-medium text-right">{{ $ticket->assignee->name ?? 'Unassigned' }}</dd></div>
                @if($ticket->vendor)
                <div class="flex justify-between"><dt class="text-gray-500">Vendor</dt><dd class="font-medium text-right">{{ $ticket->vendor->name }}</dd></div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Vendor Ref #</dt>
                    <dd class="font-mono text-xs text-right {{ $ticket->vendor_reference ? 'text-gray-800' : 'text-gray-400 italic' }}">
                        {{ $ticket->vendor_reference ?: 'not set' }}
                    </dd>
                </div>
                @can('updateStatus', $ticket)
                <form method="POST" action="{{ route('tickets.vendorRef', $ticket) }}" class="flex gap-1 pt-1">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="vendor_reference" value="{{ $ticket->vendor_reference }}" maxlength="100"
                           placeholder="e.g. DELL-2026-0042"
                           class="flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-brand-400">
                    <button type="submit" class="text-white px-2 py-1 rounded text-xs" style="background:#0056B3;">Save</button>
                </form>
                @error('vendor_reference') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @endcan
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Contact</dt>
                    <dd class="text-right text-xs">
                        {{ $ticket->employee_contact_name }}
                        @if($ticket->employee_contact_employee_id)
                            <br><span class="text-gray-500">EMP: {{ $ticket->employee_contact_employee_id }}</span>
                        @endif
                        <br><span class="text-gray-500">{{ $ticket->employee_contact_phone }}</span>
                        @if($ticket->employee_contact_email)
                            <br><span class="text-gray-400">{{ $ticket->employee_contact_email }}</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Created</dt><dd class="text-gray-600 text-right">{{ $ticket->created_at->format('d M Y, H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">TAT Deadline</dt>
                    <dd class="{{ $ticket->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600' }} text-right">{{ $ticket->tat_deadline->format('d M Y, H:i') }}</dd>
                </div>
                @if(!$ticket->isOnHold())
                <div>
                    <div class="flex justify-between text-xs mb-1"><span class="text-gray-500">TAT Progress</span><span class="text-gray-600">{{ $ticket->tatProgress() }}%</span></div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $ticket->tatProgress() >= 100 ? 'bg-red-500' : ($ticket->tatProgress() >= 75 ? 'bg-yellow-500' : 'bg-green-500') }}"
                            style="width: {{ min(100, $ticket->tatProgress()) }}%"></div>
                    </div>
                </div>
                @endif
                @if($ticket->hold_total_seconds > 0)
                <div class="flex justify-between"><dt class="text-gray-500">Hold time</dt><dd class="text-purple-600 font-medium text-right">{{ gmdate('H:i', $ticket->hold_total_seconds) }} h</dd></div>
                @endif
            </dl>
        </div>

        @can('assign', $ticket)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Re-assign Ticket</h3>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                @csrf
                <select name="assigned_to" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">Select resolver...</option>
                    @foreach(\App\Models\User::where('role','resolver')->where('is_active', true)->orderBy('name')->get() as $u)
                    <option value="{{ $u->id }}" {{ $ticket->assigned_to == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->resolver_level ?: 'resolver' }}{{ $u->assigned_support_type ? ' · '.$u->assigned_support_type : '' }})
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">Assign</button>
            </form>
        </div>
        @endcan

        {{-- Employee attachments panel: view-only for resolvers, upload for employees --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 text-sm">Attachments</h3>
            <p class="text-xs text-gray-400 mb-3">Files submitted by the employee with the ticket.</p>

            @forelse($initialAttachments as $att)
            <div class="mb-2">
                @if($att->isImage())
                <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="block">
                    <img src="{{ Storage::url($att->file_path) }}"
                         alt="{{ $att->file_name }}"
                         class="w-full rounded border border-gray-200 object-cover max-h-48 hover:opacity-90 transition">
                </a>
                <p class="text-xs text-gray-500 mt-1 truncate">{{ $att->file_name }}</p>
                @else
                <a href="{{ Storage::url($att->file_path) }}" target="_blank"
                   class="flex items-center gap-2 px-3 py-2 rounded border border-gray-200 bg-gray-50 hover:bg-gray-100 text-xs text-gray-700 transition">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <span class="truncate">{{ $att->file_name }}</span>
                    <span class="ml-auto text-gray-400 flex-shrink-0">{{ number_format($att->file_size / 1024, 0) }} KB</span>
                </a>
                @endif
            </div>
            @empty
            <p class="text-xs text-gray-400">No attachments.</p>
            @endforelse

            @can('attach', $ticket)
            <form method="POST" action="{{ route('tickets.attachment', $ticket) }}" enctype="multipart/form-data" class="mt-3 border-t pt-3">
                @csrf
                <label class="block text-xs text-gray-500 mb-1">Add attachment</label>
                <input type="file" name="attachment" class="text-sm text-gray-500 mb-2 block w-full">
                <button type="submit" class="w-full bg-gray-100 text-gray-700 py-2 rounded text-sm hover:bg-gray-200">Upload</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
