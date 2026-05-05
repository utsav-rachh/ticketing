@extends('layouts.app')
@section('title', 'Expense Approvals')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">Expense Approvals</h2>
    <div class="flex gap-2 text-sm">
        @foreach(['pending','approved','rejected'] as $s)
        <a href="{{ route('expenses.approvals', ['status' => $s]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium {{ $status === $s ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})
        </a>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Ticket</th>
                <th class="px-4 py-3 text-left">Description</th>
                <th class="px-4 py-3 text-left">Amount</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Submitted by</th>
                <th class="px-4 py-3 text-left">Approver</th>
                <th class="px-4 py-3 text-left">Invoice</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($expenses as $exp)
            <tr class="hover:bg-gray-50 align-top">
                <td class="px-4 py-3 font-mono text-xs">
                    <a href="{{ route('tickets.show', $exp->ticket) }}" class="text-brand-600 hover:underline">{{ $exp->ticket->ticket_number }}</a>
                </td>
                <td class="px-4 py-3 text-gray-700">{{ $exp->description }}</td>
                <td class="px-4 py-3 font-semibold">&#8377;{{ number_format($exp->amount, 2) }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $exp->expense_date?->format('d M Y') }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $exp->addedBy->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $exp->requestedApprover->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">
                    @if($exp->invoice_path)
                    <a href="{{ Storage::url($exp->invoice_path) }}" target="_blank" class="text-brand-600 hover:underline">view</a>
                    @else — @endif
                </td>
                <td class="px-4 py-3 text-right">
                    @if($exp->status === 'pending')
                    <form method="POST" action="{{ route('expenses.approve', $exp) }}" class="inline">
                        @csrf
                        <button class="text-xs bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('expenses.reject', $exp) }}" class="inline"
                          onsubmit="this.querySelector('[name=rejection_reason]').value = prompt('Reason for rejection?') || ''; return this.querySelector('[name=rejection_reason]').value.length > 0;">
                        @csrf
                        <input type="hidden" name="rejection_reason">
                        <button class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reject</button>
                    </form>
                    @else
                    <span class="text-xs text-gray-500">
                        {{ ucfirst($exp->status) }} by {{ $exp->approvedBy->name ?? '—' }}<br>
                        @if($exp->rejection_reason)<em>{{ $exp->rejection_reason }}</em>@endif
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No {{ $status }} expenses routed to you.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $expenses->links() }}</div>
</div>
@endsection
